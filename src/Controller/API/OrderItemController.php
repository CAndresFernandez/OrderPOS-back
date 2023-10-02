<?php

namespace App\Controller\API;

use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderItemController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/api/order-items/add/{id}", name="app_api_order_item_add", methods={"PUT"})
     */
    public function increment(OrderItem $orderItem): Response
    {
        $orderItem->setQuantity($orderItem->getQuantity() + 1);
        $this->em->persist($orderItem);
        $this->em->flush();
        $order = $orderItem->getRelatedOrder();
        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/order-items/remove/{id}", name="app_api_order_item_add", methods={"PUT"})
     */
    public function decrement(OrderItem $orderItem): Response
    {
        $order = $orderItem->getRelatedOrder();
        if ($orderItem->getQuantity() === 1) {
            $order->removeOrderItem($orderItem);
            $this->em->remove($orderItem);
        } else {
            $orderItem->setQuantity($orderItem->getQuantity() - 1);
            $this->em->persist($orderItem);
        }
        $this->em->flush();
        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }
}
