<?php

namespace App\Controller\API;

use App\Entity\Table;
use App\Entity\User;
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
    public function listTablesByUser(User $user, TableRepository $tableRepository): JsonResponse
    {
        //  récupérer les tables
        $tables = $tableRepository->findAllByUser($user);

        // on retourne les tables en json
        return $this->json($tables, Response::HTTP_OK, [], ["groups" => "tables"]);
    }

    /**
     * @Route("/api/tables/{id}/status", name="app_api_table_status", methods={"PUT"})
     */
    public function toggleStatus(Table $table, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($table->isActive()) {
            $table->setActive(false);
        } else {
            $table->setActive(true);
        }

        $entityManager->persist($table);
        $entityManager->flush();
        return $this->json($table, Response::HTTP_OK, [], ["groups" => "tables"]);
    }
}
