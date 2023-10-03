<?php

namespace App\Controller\API;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/api/categories", name="app_api_category_list")
     */
    public function list(CategoryRepository $categoryRepository): JsonResponse
    {
        //  récupérer les categories
        $categories = $categoryRepository->findBy(['active' => true]);

        // on retour les catégories en json
        return $this->json($categories, Response::HTTP_OK, [], ["groups" => "categories"]);
    }
}
