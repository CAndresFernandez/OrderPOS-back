<?php

namespace App\Controller\API;

use App\Entity\OrderItem;
<<<<<<< HEAD
use App\Service\OrderItemService;
=======
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
>>>>>>> dev
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class OrderItemController extends AbstractController
{
    private $orderItemService;

    public function __construct(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }

    /**
     * @Route("/api/order-items/add/{id}", name="app_api_order_item_add", methods={"PUT"})
     * @param object $orderItem the orderItem to modify
     */
    public function increment(OrderItem $orderItem): Response
    {
        if ($orderItem->isSent()) {
            return $this->json(["error" => "article déjà envoyé"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderItemService->add($orderItem);

        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/order-items/remove/{id}", name="app_api_order_item_remove", methods={"PUT"})
     */
    public function decrement(OrderItem $orderItem): Response
    {
        if ($orderItem->isSent()) {
            return $this->json(["error" => "article déjà envoyé"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderItemService->remove($orderItem);

        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }

    /**
     * @Route("/api/order-items/comment/{id}", name="app_api_order_item_comment", methods={"PUT"})
     */
    public function comment(OrderItem $orderItem, Request $request, SerializerInterface $serializer): Response
    {
        if ($orderItem->isSent()) {
            return $this->json(["error" => "article déjà envoyé"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $newOrderItem = $serializer->deserialize(
                $request->getContent(),
                OrderItem::class,
                'json'
            );
            $newComment = $newOrderItem->getComment();
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "json invalide"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderItemService->comment($orderItem, $newComment);

        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }
}
