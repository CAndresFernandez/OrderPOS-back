<?php

namespace App\Controller\API;

use App\Repository\TableRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TableController extends AbstractController
{
    /**
     * @Route("/api/tables", name="app_api_table_list")
     */
    public function list(TableRepository $tableRepository): JsonResponse
    {
        //  récupérer les tables
        $movies = $tableRepository->findAll();

        // on retour les films en json
        return $this->json($movies, Response::HTTP_OK);
    }
}
