<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClosedOrderRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClosedOrderRepository::class)
 */
class ClosedOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"closed"})
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     * @Groups({"closed"})
     */
    private $items = [];

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"closed"})
     */
    private $paid;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, options={"unsigned"=true})
     * @Groups({"closed"})
     */
    private $total;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Groups({"closed"})
     */
    private $count;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"closed"})
     */
    private $userId;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
