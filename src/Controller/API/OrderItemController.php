<?php

namespace App\Controller\API;

use App\Entity\OrderItem;
use App\Service\OrderItemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
    public function increment(OrderItem $orderItem, SerializerInterface $serializer, HubInterface $hub): Response
    {
        if ($orderItem->isSent()) {
            return $this->json(["error" => "article déjà envoyé"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderItemService->add($orderItem);

        $update = new Update(
            $_SERVER['BASE_URL'] . '/api/order-items/add/{id}',
            $serializer->serialize($orderItem, 'json', ['groups' => 'orders'])
            // json_encode([
            //     'id' => $orderItem->getId(),
            //     'quantity' => $orderItem->getQuantity(),
            //     'comment' => $orderItem->getComment(),
            // ])
        );

        $hub->publish($update);

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
