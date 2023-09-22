<?php

namespace App\Controller\API;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @Route("/api/categories", name="app_api_category_show")
     */
    public function list(CategoryRepository $categoryRepository): JsonResponse
    {
        //  récupérer les categoriess
        $categories = $categoryRepository->findAll();

        // on retour les catégories en json
        return $this->json($categories, Response::HTTP_OK);
    }
}
