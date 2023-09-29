<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back")
 */
class MainController extends AbstractController
{

    /**
     * @Route("/", name="app_back_home")
     */
    public function home()
    {
        return $this->render("back/main/home.html.twig");
    }

}
