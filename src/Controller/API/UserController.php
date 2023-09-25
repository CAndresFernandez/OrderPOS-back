<?php

namespace App\Controller\API;

use App\Entity\Order;
use App\Entity\Table;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="app_api_user_list", methods={"GET"})
     */
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        return $this->json($users, Response::HTTP_OK, [], ["groups" => "users"]);
    }

    /**
     * @Route("/api/users/{id}", name="app_api_user_show", methods={"GET"})
     */
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ["groups" => "users"]);
    }

    /**
     * @Route("/api/orders/{id}/users", name="app_api_user_showByOrder", methods={"GET"})
     */
    public function showByOrder(Order $order): JsonResponse
    {
        $user = $order->getUser();
        return $this->json($user, Response::HTTP_OK, [], ["groups" => "users"]);
    }

    /**
     * @Route("/api/tables/{id}/users", name="app_api_user_showByTable", methods={"GET"})
     */
    public function showByTable(Table $table): JsonResponse
    {
        $user = $table->getRelatedOrder()->getUser();
        return $this->json($user, Response::HTTP_OK, [], ["groups" => "users"]);
    }
}
