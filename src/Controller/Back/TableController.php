<?php

namespace App\Controller\Back;

use App\Entity\Table;
use App\Form\TableType;
use App\Repository\TableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/table")
 */
class TableController extends AbstractController
{
    /**
     * @Route("/", name="app_back_table_list", methods={"GET"})
     */
    public function list(TableRepository $tableRepository): Response
    {
        return $this->render('back/table/list.html.twig', [
            'tables' => $tableRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_back_table_new", methods={"GET", "POST"})
     */
    public function new (Request $request, TableRepository $tableRepository): Response
    {
        $table = new Table();
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tableRepository->add($table, true);

            return $this->redirectToRoute('app_back_table_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/table/new.html.twig', [
            'table' => $table,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_table_show", methods={"GET"})
     */
    public function show(Table $table): Response
    {
        return $this->render('back/table/show.html.twig', [
            'table' => $table,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_back_table_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Table $table, TableRepository $tableRepository): Response
    {
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tableRepository->add($table, true);

            return $this->redirectToRoute('app_back_table_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/table/edit.html.twig', [
            'table' => $table,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_table_delete", methods={"POST"})
     */
    public function delete(Request $request, Table $table, TableRepository $tableRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $table->getId(), $request->request->get('_token'))) {
            $tableRepository->remove($table, true);
        }

        return $this->redirectToRoute('app_back_table_list', [], Response::HTTP_SEE_OTHER);
    }
}
