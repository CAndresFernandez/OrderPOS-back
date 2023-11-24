<?php

namespace App\Controller\Back;

use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back")
 */
class MainController extends AbstractController
{

    /**
     * @Route("/", name="app_back_home")
     */
    public function home(CategoryRepository $categoryRepository)
    {
        $categoriesToDisplay = $categoryRepository->findBy(['active' => true], ['menu_position' => 'ASC', 'name' => 'ASC']);

        return $this->render("back/main/home.html.twig", [
            'categories' => $categoriesToDisplay,
        ]);
    }

    /**
     * @Route("/kitchen", name="app_back_kitchen")
     */
    public function kitchen(OrderRepository $orderRepository)
    {
        $ordersToDisplay = $orderRepository->findAllByStatusOne();
        $orderItems = [];
        foreach ($ordersToDisplay as $order) {
            $orderItems[] = $order->getOrderItems();
        }
        return $this->render("back/main/kitchen.html.twig", [
            'orders' => $ordersToDisplay,
            'orderItems' => $orderItems
        ]);
    }

    /**
     * @Route("/discover", name="app_back_discover")
     */
    public function discover(Request $request, Discovery $discovery): JsonResponse
    {
        // Link: <https://hub.example.com/.well-known/mercure>; rel="mercure"
        $discovery->addLink($request);

        return $this->json([
            'message' => 'done!',
        ]);
    }

    /**
     * @Route("/publish", name="app_back_publish")
     */
    // public function publish(HubInterface $hub): Response
    // {
    //     $update = new Update(
    //         'http://localhost/apo-Order/projet-8-o-commande-back/public/api/orders',
    //         json_encode(['message' => 'hello world!'])
    //     );

    //     $hub->publish($update);

    //     return new Response('published!');
    // }

}
