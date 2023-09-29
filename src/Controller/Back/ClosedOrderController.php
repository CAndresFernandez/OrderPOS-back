<?php

namespace App\Controller\Back;

use App\Entity\ClosedOrder;
use App\Form\ClosedOrderType;
use App\Repository\ClosedOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/closed/order")
 */
class ClosedOrderController extends AbstractController
{
    /**
     * @Route("/", name="app_back_closed_order_list", methods={"GET"})
     */
    public function list(ClosedOrderRepository $closedOrderRepository): Response
    {
        return $this->render('back/closed_order/list.html.twig', [
            'closed_orders' => $closedOrderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_back_closed_order_new", methods={"GET", "POST"})
     */
    public function new (Request $request, ClosedOrderRepository $closedOrderRepository): Response
    {
        $closedOrder = new ClosedOrder();
        $form = $this->createForm(ClosedOrderType::class, $closedOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $closedOrderRepository->add($closedOrder, true);

            return $this->redirectToRoute('app_back_closed_order_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/closed_order/new.html.twig', [
            'closed_order' => $closedOrder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_closed_order_show", methods={"GET"})
     */
    public function show(ClosedOrder $closedOrder): Response
    {
        return $this->render('back/closed_order/show.html.twig', [
            'closed_order' => $closedOrder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_back_closed_order_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, ClosedOrder $closedOrder, ClosedOrderRepository $closedOrderRepository): Response
    {
        $form = $this->createForm(ClosedOrderType::class, $closedOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $closedOrderRepository->add($closedOrder, true);

            return $this->redirectToRoute('app_back_closed_order_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/closed_order/edit.html.twig', [
            'closed_order' => $closedOrder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_closed_order_delete", methods={"POST"})
     */
    public function delete(Request $request, ClosedOrder $closedOrder, ClosedOrderRepository $closedOrderRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $closedOrder->getId(), $request->request->get('_token'))) {
            $closedOrderRepository->remove($closedOrder, true);
        }

        return $this->redirectToRoute('app_back_closed_order_list', [], Response::HTTP_SEE_OTHER);
    }
}
