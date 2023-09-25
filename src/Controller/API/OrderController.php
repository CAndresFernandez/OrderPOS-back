<?php

namespace App\Controller\API;

use App\Entity\Order;
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
        $orders = $OrderRepository->findAll();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_delete", methods={"DELETE"})
     */
    public function delete($id, OrderRepository $OrderRepository, EntityManagerInterface $em): JsonResponse
    {

        // Récupère la table avec l'id correspondant
        $orders = $OrderRepository->find($id);

        if (!$orders) {
            return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($orders);
        $em->flush();

        return $this->json(['message' => 'Commande supprimée avec succès.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/tables/{id}/orders", name="app_api_order_showByTable", methods={"GET"})
     */
    public function showByTable($id, OrderRepository $OrderRepository, TableRepository $tableRepository): JsonResponse
    {
        // Récupère la table avec l'id correspondant
        $tables = $tableRepository->find($id);

        // Message d'ereur si la table n'existe pas
        if (!$tables) {
            return $this->json(['message' => 'Table non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        // Récupère les order items associés à une table
        $orders = $OrderRepository->findBy(['relatedTable' => $tables]);

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/users/{id}/orders", name="app_api_order_listByUser", methods={"GET"})
     */
    public function listByUser($id, OrderRepository $OrderRepository, UserRepository $userRepository): JsonResponse
    {
        // Récupère un utilisateur grace à l'id
        $users = $userRepository->find($id);

        // Message d'ereur si la table n'existe pas
        if (!$users) {
            return $this->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Récupère les orders associés à un utilisateur
        $orders = $OrderRepository->findBy(['user' => $users]);

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/orders", name="app_api_order_new", methods={"POST"})
     */
    public function add(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, OrderRepository $orderRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $order = $serializer->deserialize($request->getContent(), Order::class, 'json',
                [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['user_id', 'relatedTable_id'],
                ]);

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
}
