<?php

namespace App\Controller\API;

use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
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
    public function show($id, ItemRepository $itemRepository): JsonResponse
    {
        $item = $itemRepository->find($id);

        return $this->json($item, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/items/{id}", name="app_api_item_toggle_status", methods={"PUT"})
     */
    public function toggleStatus(int $id, ItemRepository $itemRepository, EntityManagerInterface $entityManager): JsonResponse
    {$item = $itemRepository->find($id);

        if (!$item) {
            return $this->json(['message' => 'Article non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        if ($item->isActive()) {
            $item->setActive(false);
        } else {
            $item->setActive(true);
        }

        $entityManager->flush();
        return $this->json($item, Response::HTTP_OK, [], ["groups" => "items"]);
    }

    /**
     * @Route("/api/categories/{id}/items", name="app_api_item_listByCategory", methods={"GET"})
     */
    public function listByCategory($id, CategoryRepository $categoryRepository, ItemRepository $itemRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $items = $itemRepository->findBy(['category' => $category]);

        return $this->json($items, Response::HTTP_OK, [], ["groups" => "items"]);

    }

    /**
     * @Route("/api/orders/{id}/items", name="app_api_item_listByOrder", methods={"GET"})
     */
    public function listByOrder($id, OrderRepository $orderRepository, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

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

}
