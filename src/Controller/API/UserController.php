<?php

namespace App\Controller\Api;

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

        return $this->json([$users, Response::HTTP_OK, [], "groups" => "users"]);
    }
    /**
     * @Route("/api/orders/{id}/users", name="app_api_user_showByOrder", methods={"GET"})
     */
    public function showByOrder(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/UserController.php',
        ]);
    }
    /**
     * @Route("/api/tables/{id}/users", name="app_api_user_showByTable", methods={"GET"})
     */
    public function showByTable(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/UserController.php',
        ]);
    }
    /**
     * @Route("/api/users/{id}", name="app_api_user_show", methods={"GET"})
     */
    public function show(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/UserController.php',
        ]);
    }
}
