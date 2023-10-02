<?php

namespace App\Controller\Back;

use App\Repository\ItemRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/back")
 */
class MainController extends AbstractController
{

    /**
     * @Route("/", name="app_back_home")
     */
    public function home(ItemRepository $itemRepository, CategoryRepository $categoryRepository)
    {
        // $items = $itemRepository->findAll();
        // $categories = $categoryRepository->findAll();

        // $itemsToDisplay = $itemRepository->findAllSortByMenuPosition();
        $categoriesToDisplay = $categoryRepository->findBy(['active' => true], ['menu_position' => 'ASC', 'name' => 'ASC']);

        return $this->render("back/main/home.html.twig", [
            // 'items' => $itemsToDisplay,
            'categories' => $categoriesToDisplay,
        ]);
    }

}
