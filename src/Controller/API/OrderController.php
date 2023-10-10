<?php

namespace App\Controller\API;

use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Table;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
    public function list(OrderRepository $OrderRepository, HubInterface $hub): JsonResponse
    {
        // Récupère toutes les tables
        $orders = $OrderRepository->findAllByStatusOne();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_show", methods={"GET"})
     */
    public function show(Order $order): JsonResponse
    {
        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_delete", methods={"DELETE"})
     */
    public function delete(Order $order, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($order);
        $em->flush();

        return $this->json(['message' => 'Commande supprimée avec succès.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/{id}/orders", name="app_api_order_listByUser", methods={"GET"})
     */
    public function listByUser(User $user, OrderRepository $OrderRepository): JsonResponse
    {
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
        $orderRepository->add($order, true);

        // on retour les orders en json
        // préférence perso : je retourne l'order crée
        // norme rest : 201, Location avec le lien de la ressource
        return $this->json($order, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_api_order_list")], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{order}/items/{item}", name="app_api_order_addItem", methods={"PUT"})
     * @param object $order the order to modify
     * @param object $item the item to be added
     */
    public function addItem(Order $order, Item $item, OrderItemRepository $orderItemRepository, SerializerInterface $serializer, HubInterface $hub): JsonResponse
    {
        //je recherche si un order item sans commentaire et non envoyé existe déjà dans la commande
        $orderItem = $orderItemRepository->findBy(['item' => $item->getId(), 'relatedOrder' => $order->getId(), 'comment' => [null, ""], 'sent' => false]);

        //je modifie l'orderItem existant ou j'en créé un nouveau
        if ($orderItem) {
            $quantity = 0;
            foreach ($orderItem as $key => $value) {
                if ($key) {
                    $quantity += $value->getQuantity();
                    $order->removeOrderItem($value);
                    $orderItemRepository->remove($value, true);
                }
            }
            $orderItem[0]->setQuantity($orderItem[0]->getQuantity() + $quantity + 1);
            $orderItem[0]->setComment(null);
            $this->em->persist($order);
            $this->em->flush();
        } else { //créer un nouvelle orderItem
            $newOrderItem = new OrderItem();
            $newOrderItem->setItem($item);
            $newOrderItem->setQuantity(1);
            $order->addOrderItem($newOrderItem);
            $orderItemRepository->add($newOrderItem, true);
        }
        $update = new Update(
            'orders',
            $serializer->serialize($order, 'json', ['groups' => 'orders'])
        );

        $hub->publish($update);
        //je retourne la commande
        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}/status", name="app_api_order_status", methods={"PUT"})
     */
    public function modifyStatus(Order $order, SerializerInterface $serializer, HubInterface $hub)
    {
        $orderItems = $order->getOrderItems();


        $status = $order->getStatus();

        switch ($status) {
            case 0:
                $order->setStatus(1); // send to kitchen
                break;

            case 1:
                if ($orderItems->isEmpty()) {
                    $order->setStatus(0); // Si $orderItems est vide, revenez à l'état 0
                } else {
                    $order->setStatus(2); // Sinon, changez l'état en 2 (validation depuis la cuisine)
                    foreach ($orderItems as $orderItem) {
                        $orderItem->setSent(true);
                    }
                }
                break;

            case 2:
                $order->setStatus(1);
                break;

            default:
                // Handle any other status values if needed
                break;
        }

        $this->em->persist($order);
        $update = new Update(
            'orders',
            $serializer->serialize($order, 'json', ['groups' => 'orders'])
        );
        $this->em->flush();
        $hub->publish($update);

        return $this->json($order, Response::HTTP_OK, ["Location" => $this->generateUrl("app_api_order_show", ['id' => $order->getId()])], ["groups" => "orders"]);
    }
}
