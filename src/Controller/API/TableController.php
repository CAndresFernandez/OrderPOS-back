<?php

namespace App\Controller\API;

use App\Repository\TableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TableController extends AbstractController
{
    /**
     * @Route("/api/tables", name="app_api_table_list")
     */
    public function list(TableRepository $tableRepository): JsonResponse
    {
        //  récupérer les tables
        $tables = $tableRepository->findAll();

        // on retourne les tables en json
        return $this->json($tables, Response::HTTP_OK, [], ["groups" => "tables"]);
    }

    /**
     * @Route("api/users/{id}/tables", name="app_api_table_listByUser")
     */
    public function showTablesByUser($id, TableRepository $tableRepository): JsonResponse
    {
        //  récupérer les tables
        $tables = $tableRepository->findAllByUser($id);

        // on retourne les tables en json
        return $this->json($tables, Response::HTTP_OK, [], ["groups" => "tables"]);
    }

    /**
     * @Route("/api/tables/{id}", name="app_api_table_status", methods={"PUT"})
     */
    public function toggleStatus($id, TableRepository $tableRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return $this->json(['message' => 'Table not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($table->isActive() !== null && $table->isActive()) {
            $table->setActive(false);
        } elseif ($table->isActive() === false) {
            $table->setActive(true);
        } else {

        }

        $entityManager->flush();
        return $this->json($table, Response::HTTP_OK, [], ["groups" => "tables"]);
    }
}
