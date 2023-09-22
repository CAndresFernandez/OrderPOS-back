<?php

namespace App\Entity;

use App\Repository\TableRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TableRepository::class)
 * @ORM\Table(name="`table`")
 * @ORM\HasLifecycleCallbacks
 */
class Table
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"tables"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @Groups({"tables"})
     */
    private $number;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Groups({"tables"})
     */
    private $covers;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     * @Groups({"tables"})
     */
    private $active;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"tables"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"tables"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, mappedBy="relatedTable", cascade={"persist", "remove"})
     */
    private $relatedOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCovers(): ?int
    {
        return $this->covers;
    }

    public function setCovers(int $covers): self
    {
        $this->covers = $covers;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAt() : self
    {
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    public function setRelatedOrder(Order $relatedOrder): self
    {
        // set the owning side of the relation if necessary
        if ($relatedOrder->getTable() !== $this) {
            $relatedOrder->setTable($this);
        }

        $this->relatedOrder = $relatedOrder;

        return $this;
    }
}
