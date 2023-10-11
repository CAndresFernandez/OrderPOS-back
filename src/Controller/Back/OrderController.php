<?php

namespace App\Controller\Back;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="app_back_order_list", methods={"GET"})
     */
    public function list(OrderRepository $orderRepository): Response
    {
        return $this->render('back/order/list.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

}
