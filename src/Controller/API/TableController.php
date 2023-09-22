<?php

namespace App\Controller\API;

use App\Repository\TableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
        $tables = $tableRepository->findAll();

        // on retourne les tables en json
        return $this->json($tables, Response::HTTP_OK, [], ["groups" => "tables"]);
    }

    /**
     * @Route("api/tables/{id}", name="app_api_table_listByUser")
     */
    public function showTablesByUser($id, TableRepository $tableRepository): JsonResponse
    {
        //  récupérer les tables
        $tables = $tableRepository->findAllByUser($id);

        // on retourne les tables en json
        return $this->json($tables, Response::HTTP_OK, [], ["groups" => "tables"]);
    }

    /**
     * @Route("/api/tables/{id}", name="app_api_table_show", methods={"PUT"})
     */
    public function edit($id, Request $request, TableRepository $tableRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return $this->json(['message' => 'Article non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $table->setActive($data['active']);

        $entityManager->flush();


        return $this->json($table, Response::HTTP_OK,[], ["groups" => "tables"]);
    }
}
