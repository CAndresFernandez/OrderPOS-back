<?php
namespace App\DataFixtures\Provider;

class OrderProvider
{

    private $categories = [
        1 => 'Appetizers',
        2 => 'Main Dishes',
        3 => 'Desserts',
        4 => 'Beer',
        5 => 'Wine',
        6 => 'Pizzas',
        7 => 'Tacos',
        8 => 'Pasta',
        9 => 'Meats',
        10 => 'Seafood',
    ];

    /**
     * returns a random category
     */
    public function itemCategory($c)
    {
        return $this->categories[$c];
    }
}
