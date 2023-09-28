<?php

/**
 * @Route("/api/orders/{id}", name="app_api_order_addOrderItem", methods={"PUT"})
 * @param int $id the id of the order to modify
 */
public function addItemToOrder(
    int $id,
    SerializerInterface $serializer,
    ValidatorInterface $validator,
    OrderRepository $orderRepository,
    Request $request
): JsonResponse {
    // Retrieve the order
    $order = $this->getOrderOrReturnNotFound($id, $orderRepository);
    if (!$order) {
        return $this->json(['message' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
    } elseif ($order->getStatus() > 1) {
        return $this->json(['message' => 'Commande déjà envoyée.'], Response::HTTP_FORBIDDEN);
    }

    // Harmonize the order items collection
    $this->harmonizeOrderItems($order);

    // Process and update order items
    $newOrderItems = $this->processOrderItems($request, $serializer);
    $this->updateOrderItems($order, $newOrderItems);

    // Validate the order
    $errors = $validator->validate($order);
    if (count($errors) > 0) {
        return $this->handleValidationErrors($errors);
    }

    // Update order status and save
    $this->updateOrderStatus($order);

    return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders"]);
}

private function getOrderOrReturnNotFound(int $id, OrderRepository $orderRepository): ?Order {
    return $orderRepository->find($id);
}

private function harmonizeOrderItems(Order $order): void {
    // Implement the logic to harmonize order items here
}

private function processOrderItems(Request $request, SerializerInterface $serializer): array {
    // Implement the logic to process order items from the request here
}

private function updateOrderItems(Order $order, array $newOrderItems): void {
    // Implement the logic to update order items here
}

private function handleValidationErrors(ConstraintViolationListInterface $errors): JsonResponse {
    // Implement the logic to handle validation errors here
}

private function updateOrderStatus(Order $order): void {
    // Implement the logic to update the order status here
}

?>