<?php

namespace App\Controller\API;

use App\Entity\ClosedOrder;
use App\Entity\Order;
use App\Repository\ClosedOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClosedOrderController extends AbstractController
{
    /**
     * @Route("api/closed/{id}", name="app_api_closed_show", methods={"GET"})
     */
    public function show(ClosedOrder $closedOrder): JsonResponse
    {
        // on retourne les closedOrders en json
        return $this->json($closedOrder, Response::HTTP_OK, [], ["groups" => "closed"]);
    }

    /**
     * @Route("/api/closed", name="app_api_closed_list")
     */
    public function list(ClosedOrderRepository $closedOrderRepository): JsonResponse
    {
        //  récupérer les closedOrders
        $closedOrders = $closedOrderRepository->findAll();

        // on retour les catégories en json
        return $this->json($closedOrders, Response::HTTP_OK);
    }

    /**
     * @Route("/api/orders/{id}/closed", name="app_api_closed_add", methods={"POST"})
     */
    public function add(Order $order, ClosedOrderRepository $closedOrderRepository, ValidatorInterface $validator): JsonResponse
    {
        $server = $order->getUser();
        $serverName = $server->getFirstname() . ' ' . $server->getLastname();

        $closedOrder = new ClosedOrder;
        $total = 0;
        $count = 0;
        $closedOrderItems = [];
        foreach ($order->getOrderItems() as $orderItem) {
            $item = $orderItem->getItem();
            $totalOrderItem = $item->getPrice() * $orderItem->getQuantity();
            $total += $totalOrderItem;
            $count += $orderItem->getQuantity();
            $currentOrderItem = [
                "name" => $item->getName(),
                "quantity" => $orderItem->getQuantity(),
                "price" => $item->getPrice(),
                "total" => $totalOrderItem,
            ];
            $closedOrderItems[] = $currentOrderItem;
        }

        $closedOrder->setItems($closedOrderItems);
        $closedOrder->setTotal($total);
        $closedOrder->setCount($count);
        $closedOrder->setUserId($serverName);
        $closedOrder->setPaid(true);

        $errors = $validator->validate($closedOrder);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // je me crée un tableau avec les erreurs en valeur et les champs concernés en index
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $closedOrderRepository->add($closedOrder, true);

        // on retourne le closedOrder créé en json
        // norme rest : 201, Location avec le lien de la ressource
        return $this->json($closedOrder, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_api_closed_show", ["id" => $closedOrder->getId()])]);
    }
}
