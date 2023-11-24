<?php

namespace App\Controller\API;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Order;
use App\Repository\ItemRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    /**
     * @Route("/api/items", name="app_api_item_list", methods={"GET"})
     */
    public function list(ItemRepository $itemRepository): JsonResponse
    {
        $items = $itemRepository->findAll();

        return $this->json($items, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/items/{id}", name="app_api_item_show", methods={"GET"})
     */
    public function show(Item $item): JsonResponse
    {
        return $this->json($item, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/categories/{id}/items", name="app_api_item_listByCategory", methods={"GET"})
     */
    public function listByCategory(Category $category, ItemRepository $itemRepository): JsonResponse
    {
        $items = $itemRepository->findBy(['category' => $category]);
        return $this->json($items, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/orders/{id}/items", name="app_api_item_listByOrder", methods={"GET"})
     */
    public function listByOrder(Order $order, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $orderItems = $orderItemRepository->findBy(['order_id' => $order]);
        $items = [];
        foreach ($orderItems as $orderItem) {
            $item = $orderItem->getItem();
            if ($item) {
                $items[] = $item;
            }
        }

        return $this->json($items, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/items/{id}/status", name="app_api_item_status", methods={"PUT"})
     */
    public function toggleStatus(Item $item, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($item->isActive()) {
            $item->setActive(false);
        } else {
            $item->setActive(true);
        }
        $entityManager->persist($item);
        $entityManager->flush();

        return $this->json($item, Response::HTTP_OK, [], ["groups" => "items"]);
    }

}
