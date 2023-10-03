<?php

namespace App\Service;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;


class OrderItemService
{
    private $em;
    private $orderItemRepository;

    public function __construct(EntityManagerInterface $em, OrderItemRepository $orderItemRepository)
    {
        $this->em = $em;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Add an orderItem
     */
    public function add($orderItem)
    {
        $orderItem->setQuantity($orderItem->getQuantity() + 1);
        $this->em->persist($orderItem);
        $this->em->flush();
        $order = $orderItem->getRelatedOrder();
        return $order;
    }
    /**
     * Remove an orderItem
     */
    public function remove($orderItem)
    {
        $order = $orderItem->getRelatedOrder();
        if ($orderItem->getQuantity() === 1) {
            $order->removeOrderItem($orderItem);
            $this->em->remove($orderItem);
        } else {
            $orderItem->setQuantity($orderItem->getQuantity() - 1);
            $this->em->persist($orderItem);
        }
        $this->em->flush();
        return $order;
    }

    /**
     * Comment an orderItem
     */
    public function comment($orderItem, $newComment)
    {
        $order = $orderItem->getRelatedOrder();
        //Je cherche s'il existe un orderItem avec le meme commentaire
        $existingOrderItem = $this->orderItemRepository->findBy(['relatedOrder' => $order, 'comment' => $newComment, 'sent' => false]);

        if ($existingOrderItem) { //Si j'en trouve un ou plus
            $quantity = $existingOrderItem[0]->getQuantity(); //je récupère la quantité du premier
            foreach ($existingOrderItem as $key => $value) {
                if ($key) { //s'il existe des doublons je les regroupe et je les supprime
                    $quantity += $value->getQuantity();
                    $order->removeOrderItem($value);
                    $this->orderItemRepository->remove($value, true);
                }
                $existingOrderItem[0]->setQuantity($quantity + $orderItem->getQuantity()); //j'ajoute la quantité de l'orderItem sélectionné
                // à l'orderItem existant ayant le meme commentaire
                //je supprime l'orderItem modifié
                $order->removeOrderItem($orderItem);
                $this->orderItemRepository->remove($orderItem, true);
            }
        } else { //s'il n'existe pas d'order Item avec le commentaire envoyé je modifie l'existant
            $orderItem->setComment($newComment);
            $this->em->persist($orderItem);
        }
        $this->em->flush();
        return $order;
    }
}
