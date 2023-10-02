<?php

namespace App\Controller\API;

use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Table;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/api/orders", name="app_api_order_list", methods={"GET"})
     */
    public function list(OrderRepository $OrderRepository): JsonResponse
    {
        // Récupère toutes les tables
        $orders = $OrderRepository->findAllByStatusOne();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_delete", methods={"DELETE"})
     */
    public function delete($id, OrderRepository $OrderRepository, EntityManagerInterface $em): JsonResponse
    {

        // Récupère la table avec l'id correspondant
        $order = $OrderRepository->find($id);

        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($order);
        $em->flush();

        return $this->json(['message' => 'Commande supprimée avec succès.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/tables/{id}/orders", name="app_api_order_showByTable", methods={"GET"})
     */
    public function showByTable($id, OrderRepository $OrderRepository, TableRepository $tableRepository): JsonResponse
    {
        // Récupère la table avec l'id correspondant
        $table = $tableRepository->find($id);

        // Message d'erreur si la table n'existe pas
        if (!$table) {
            return $this->json(['message' => 'Table non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        // Récupère les order items associés à une table
        $order = $OrderRepository->findBy(['relatedTable' => $table]);

        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/users/{id}/orders", name="app_api_order_listByUser", methods={"GET"})
     */
    public function listByUser($id, OrderRepository $OrderRepository, UserRepository $userRepository): JsonResponse
    {
        // Récupère un utilisateur grace à l'id
        $user = $userRepository->find($id);

        // Message d'erreur si la table n'existe pas
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Récupère les orders associés à un utilisateur
        $orders = $OrderRepository->findBy(['user' => $user]);

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders", name="app_api_order_new", methods={"POST"})
     */
    public function add(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, OrderRepository $orderRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $order = $serializer->deserialize(
                $request->getContent(),
                Order::class,
                'json',
                [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['user_id', 'relatedTable_id'],
                ]
            );

            $table = $this->em->getRepository(Table::class)->find($data['relatedTable_id']);
            $user = $this->em->getRepository(User::class)->find($data['user_id']);

            $order
                ->setRelatedTable($table)
                ->setUser($user);

            // dd($order);
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "json invalide"], Response::HTTP_BAD_REQUEST);
        }

        // je check si mon order contient des erreurs
        $errors = $validator->validate($order);

        // est ce qu'il y a au moins une erreur
        if (count($errors) > 0) {

            foreach ($errors as $error) {
                // je me crée un tableau avec les erreurs en valeur et les champs concernés en index
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ! j'arrive je sais que mes constraints sont bien passés
        $orderRepository->add($order, true);

        // on retour les orders en json
        // préférence perso : je retourne l'order crée
        // norme rest : 201, Location avec le lien de la ressource
        return $this->json($order, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_api_order_list")], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_addOrderItem", methods={"PUT"})
     * @param int $id the id of the order to modify
     */
    public function addItemToOrder(int $id, SerializerInterface $serializer, ValidatorInterface $validator, OrderRepository $orderRepository, Request $request): JsonResponse
    {
        //je récupère la commande en cours
        $order = $orderRepository->find($id);
        //je vérifie que la commande en cours existe bien
        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        } elseif ($order->getStatus() > 1) {
            return $this->json(['message' => 'Commande déjà envoyée.'], Response::HTTP_FORBIDDEN);
        }

        // je récupère la collection des orderItems de la commande actuelle (peut être vide si nouvelle commande par ex)
        $oldOrderItems = $order->getOrderItems()->getValues();
        //cette boucle sert à harmoniser la collection en cas de données rajouter en brut dans la bdd
        //cette boucle ne devrait normalement pas servir dans l'usage normal de l'application
        dump("collection d'orderItems de order initiale avant factorisation", $oldOrderItems);
        $orderItemsToDelete = [];
        foreach ($oldOrderItems as $key => $oldOrderItem) {
            // pour chaque orderItem je récupère son id, sa quantité, son commentaire et l'id de l'item correspondant
            $itemId = $oldOrderItem->getItem()->getId();
            $id = $oldOrderItem->getId();
            $comment = $oldOrderItem->getComment();
            $quantity = $oldOrderItem->getQuantity();
            $orderItemKeyToDelete = [];
            foreach ($oldOrderItems as $keyB => $oldOrderItemB) {
                //pour chaque orderItem je cherche si un autre orderItem relié au même item et ayant le même commentaire existe
                //si c'est le cas j'ajoute les quantités du second au premier puis je viens supprimer le second de la collection
                //d'orderItems
                if (($itemId == $oldOrderItemB->getItem()->getId()) && ($id != $oldOrderItemB->getId()) && ($comment == $oldOrderItemB->getComment())) {
                    $quantity += $oldOrderItemB->getQuantity();
                    $orderItemKeyToDelete[] = $keyB;
                    $orderItemsToDelete[] = $oldOrderItemB;
                    // dump($key, 'reste au profit de', $keyB, $itemId, $oldOrderItemB->getItem()->getId());
                }
            }
            foreach ($orderItemKeyToDelete as $keyToDelete) {
                unset($oldOrderItems[$keyToDelete]);
            }
            $oldOrderItem->setQuantity($quantity);
        }
        // dump($oldOrderItems, $order->getOrderItems()->getValues());
        //je supprime les orderItems qui ont été factorisés
        foreach ($orderItemsToDelete as $orderItem) {
            $this->em->getRepository(OrderItem::class)->remove($orderItem);
            $order->removeOrderItem($orderItem);
        }
        // dump($oldOrderItems, $order->getOrderItems()->getValues());
        //je vérifie que les opérations précédentes

        // je récupère la liste des orderItems de la requête
        $data = json_decode($request->getContent(), true);
        //je créé un tableau vide pour acceuillir la liste actualisée des orderItems
        $newOrderItems = [];
        foreach ($data as $orderItem) {
            $newItem = json_encode($orderItem);
            $newOrderItem = $serializer->deserialize($newItem, OrderItem::class, 'json', [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['item'],
            ]);
            $item = $this->em->getRepository(Item::class)->find($orderItem['item']);
            $newOrderItem->setItem($item);
            //La liste obtenue contient des orderItems sans Id et sans orderId
            $newOrderItems[] = $newOrderItem;
        }
        dump("collection d'orderItems de order", $order->getOrderItems()->getValues(), "oldOrderItems virtuel", $oldOrderItems, "newOrderItems virtuel", $newOrderItems);
        // dd($oldOrderItems, $newOrderItems);
        // je créé une nouvelle liste vide d'orderItems mise à jour
        $updatedOrderItems = [];
        //je compare l'ancienne et la nouvelle liste d'orderItems
        //pour remplir $updatedOrderItems
        $oldOrderItemToSave = []; //je créé un tableau avec les clés des orderItems existants qui seront conservés
        foreach ($newOrderItems as $newOrderItem) {
            $itemId = $newOrderItem->getItem()->getId();
            $comment = $newOrderItem->getComment();
            $quantity = $newOrderItem->getQuantity();
            $newOrderItemToConserve = true;
            foreach ($oldOrderItems as $key => $oldOrderItem) {
                //pour chaque newOrderItem je cherche si un oldOrderItem relié au même item et ayant le même commentaire existe
                //si c'est le cas je viens actualiser l'ancien avec les valeurs du nouveau puis je l'ajoute à la nouvelle liste MAJ
                if (($itemId == $oldOrderItem->getItem()->getId()) && ($comment == $oldOrderItem->getComment())) {
                    //
                    //
                    // ICI JE PEUX RAJOUTER UN FILTRE ISSENT
                    //
                    //
                    $oldOrderItem->setQuantity($quantity); //j'actualise la quantité
                    $oldOrderItemToSave[] = $key;
                    $updatedOrderItems[] = $oldOrderItem;
                    $newOrderItemToConserve = false; //si je trouve un ancien order item correspondant alors je ne conserve pas le nouveau
                }
            }
            if ($newOrderItemToConserve) {
                $updatedOrderItems[] = $newOrderItem;
            }
        }
        dump("updatedOrderItems", $updatedOrderItems);
        //je supprime les orderItems conservés de $oldOrderItems
        foreach ($oldOrderItemToSave as $keyToDelete) {
            unset($oldOrderItems[$keyToDelete]);
        }
        dump("orderItemsToSave", $oldOrderItemToSave, "oldOrderItems", $oldOrderItems);
        //je supprime les oldOrderItems n'étant pas conservés
        foreach ($oldOrderItems as $oldOrderItem) {
            $this->em->getRepository(OrderItem::class)->remove($oldOrderItem);
            $order->removeOrderItem($oldOrderItem);
        }
        $this->em->persist($order);
        foreach ($updatedOrderItems as $updatedOrderItem) {
            $order->addOrderItem($updatedOrderItem);
        }

        // dd($order);

        // je check si mon order contient des erreurs
        $errors = $validator->validate($order);

        // est ce qu'il y a au moins une erreur
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // je me crée un tableau avec les erreurs en valeur et les champs concernés en index
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->getStatus() === 0) {
            $order->setStatus(1);
        }

        $this->em->persist($order);
        $this->em->flush();

        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}/status", name="app_api_order_modifyStatus", methods={"PUT"})
     */
    public function modifyStatus(int $id, OrderRepository $orderRepository)
    {
        $order = $orderRepository->find($id);
        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        } elseif ($order->getStatus() == 1) {
            $order->setStatus(2);
            return $this->json(['message' => 'Commande déjà envoyée.'], Response::HTTP_FORBIDDEN, ["Location" => $this->generateUrl("app_api_order_list")], ["groups" => "orders"]);
        }
    }
}
