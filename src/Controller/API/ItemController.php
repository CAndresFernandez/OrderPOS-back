<?php

namespace App\Controller\API;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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

}

