<?php

namespace App\Controller\API;

use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{
    /**
     * @Route("/api/items", name="app_api_item_list", methods={"GET"})
     */
    public function list(ItemRepository $itemRepository): JsonResponse
    {
        $items = $itemRepository->findAll();

        return $this->json($items, Response::HTTP_OK,[], ["groups" => "items"]);
    }

    /**
     * @Route("/api/items/{id}", name="app_api_item_show", methods={"GET"})
     */
    public function show($id, ItemRepository $itemRepository): JsonResponse
    {
        $item = $itemRepository->find($id);

        return $this->json($item, Response::HTTP_OK,[], ["groups" => "items"]);
    }

    /**
     * @Route("/api/items/{id}", name="app_api_item_show", methods={"PUT"})
     */
    public function edit($id, Request $request, ItemRepository $itemRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $itemRepository->find($id);

        if (!$item) {
            return $this->json(['message' => 'Article non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $item->setActive($data['active']);

        $entityManager->flush();


        return $this->json($item, Response::HTTP_OK,[], ["groups" => "items"]);
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


}

