<?php

namespace App\DataFixtures;

use App\DataFixtures\Provider\OrderProvider;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Table;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create an instance of Faker
        $faker = Factory::create();
        // create an instance of the provider OrderProvider
        $provider = new OrderProvider();

        // ! USER
        // ADMIN
        $roleAdmin = new User();
        $roleAdmin->setFirstname($faker->firstname());
        $roleAdmin->setLastname($faker->lastname());
        $roleAdmin->setLogin(99);
        $roleAdmin->setRoles(["ROLE_ADMIN"]);
        $roleAdmin->setPassword($this->passwordHasher->hashPassword($roleAdmin, "2222"));
        // $roleAdmin->setCreatedAt(new DateTimeImmutable());
        $manager->persist($roleAdmin);

        // MANAGER
        for ($u = 1; $u <= 2; $u++) {
            $roleManager = new User();
            $roleManager->setFirstname($faker->firstname());
            $roleManager->setLastname($faker->lastname());
            $roleManager->setLogin($u);
            $roleManager->setRoles(["ROLE_MANAGER"]);
            $roleManager->setPassword($this->passwordHasher->hashPassword($roleManager, "1111"));
            // $roleManager->setCreatedAt(new DateTimeImmutable());
            $manager->persist($roleManager);
        };

        // USER (SERVER)
        $userList = [];
        for ($u = 3; $u <= 9; $u++) {
            $roleUser = new User();
            $roleUser->setFirstname($faker->firstname());
            $roleUser->setLastname($faker->lastname());
            $roleUser->setLogin($u);
            $roleUser->setPassword($this->passwordHasher->hashPassword($roleUser, "0000"));
            $roleUser->setRoles(["ROLE_USER"]);
            // $roleUser->setCreatedAt(new DateTimeImmutable());
            $userList[] = $roleUser;
            $manager->persist($roleUser);
        };

        // ! TABLE
        $tableList = [];
        for ($t = 1; $t <= 20; $t++) {
            $table = new Table();
            $table->setNumber($t);
            $table->setCovers($faker->numberBetween(2, 4));
            $table->setActive(true);
            // $table->setCreatedAt(new DateTimeImmutable());
            $tableList[] = $table;
            $manager->persist($table);
        }

        // ! CATEGORY
        $categoryList = [];
        for ($c = 1; $c <= 10; $c++) {
            $category = new Category();
            $category->setName($provider->itemCategory($c));
            $category->setMenuPosition($c);
            $category->setActive(true);
            // $category->setCreatedAt(new DateTimeImmutable());
            $categoryList[] = $category;
            $manager->persist($category);
        }

        // ! ITEM
        $itemList = [];
        for ($i = 1; $i <= 20; $i++) {
            $item = new Item();
            $item->setName($faker->word());
            $item->setPrice($faker->randomFloat(2, 5, 18));
            $item->setDescription($faker->sentence());
            $item->setActive(true);
            $item->setCategory($categoryList[mt_rand(1, 5)]);
            // $item->setCreatedAt(new DateTimeImmutable());
            $itemList[] = $item;
            $manager->persist($item);
        }

        // ! ORDER
        $orderList = [];
        for ($o = 1; $o <= 12; $o++) {
            $order = new Order();
            $order->setStatus($faker->numberBetween(1, 2));
            $order->setRelatedTable($tableList[$o - 1]);
            $order->setUser($userList[mt_rand(1, 4)]);
            // $order->setCreatedAt(new DateTimeImmutable());
            $orderList[] = $order;

            // ! ORDER items
            for ($oi = 1; $oi < mt_rand(2, 8); $oi++) {
                $orderItem = new OrderItem();
                $orderItem->setQuantity(mt_rand(1, 3));
                $orderItem->setComment($faker->words(mt_rand(0, 3), true));
                $orderItem->setItem($itemList[mt_rand(0, 19)]);
                if ($order->getStatus() == 2) {
                    $orderItem->setSent(true);
                }
                $order->addOrderItem($orderItem);
                $manager->persist($orderItem);
            }
            $manager->persist($order);
        }
        $manager->flush();
    }
}
