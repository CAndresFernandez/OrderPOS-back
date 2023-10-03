<?php

namespace App\Controller\API;

use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

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

    /**
     * @Route("/api/order-items/comment/{id}", name="app_api_order_item_add", methods={"PUT"})
     */
    public function comment(OrderItem $orderItem, OrderItemRepository $orderItemRepository, Request $request, SerializerInterface $serializer): Response
    {
        //on part du principe qu'on reçoit un orderItem existant avec ou sans commentaire
        //on reçoit en Json une quantité et un commentaire
        //on créé un nouvel orderItem avec la quantité et le commentaire reçu si besoin
        //sinon on ajuste l'orderItem existant ou on modifie un autre orderItem
        //on ajuste l'orderItem envoyé en fonction de ces paramètres
        //on contrôle la cohérence de la demande (quantité, commentaires)
        $order = $orderItem->getRelatedOrder();
        $data = json_decode($request->getContent(), true);
        try {
            $newOrderItem = $serializer->deserialize(
                $request->getContent(),
                OrderItem::class,
                'json'
            );
            $newQuantity = $newOrderItem->getQuantity();
            $newComment =  $newOrderItem->getComment();
        } catch (NotEncodableValueException $e) {
            return $this->json(["error" => "json invalide"], Response::HTTP_BAD_REQUEST);
        }
        if ($newQuantity > $orderItem->getQuantity()) {
            return $this->json(["error" => "quantité erronée"], Response::HTTP_BAD_REQUEST);
        }
        $existingOrderItem = $orderItemRepository->findBy(['relatedOrder' => $order, 'comment' => $newComment, 'sent' => false]);
        if ($existingOrderItem) {
            $quantity = $orderItem[0]->getQuantity();
            foreach ($existingOrderItem as $key => $value) {
                if ($key) { //s'il existe des doublons je les regroupe et je les supprime
                    $quantity += $value->getQuantity();
                    $order->removeOrderItem($value);
                    $orderItemRepository->remove($value, true);
                }
                $existingOrderItem[0]->setQuantity($quantity + $newQuantity);
            }
        } else {
            $orderItem->setQuantity($orderItem->getQuantity() - 1);
            $this->em->persist($orderItem);
        }
        $this->em->flush();
        return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
    }
}
