<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"orders"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", options={"default"=0, "unsigned"=true})
     * @Groups({"orders"})
     * @Assert\PositiveOrZero
     * @Assert\NotBlank
     */
    private $status = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"orders"})
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"orders"})
     * @Assert\DateTime
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="relatedOrder", orphanRemoval=true, cascade={"persist"})
     * @Groups({"orders"})
     */
    private $orderItems;

    /**
     * @ORM\OneToOne(targetEntity=Table::class, inversedBy="relatedOrder", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"orders"})
     */
    private $relatedTable;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"users", "orders"})
     */
    private $user;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultStatus(): self
    {
        if ($this->status === null) {
            $this->status = 0;
        }
        return $this;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt() : self
    {
        $this->createdAt = new \DateTimeImmutable();

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

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setRelatedOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getRelatedOrder() === $this) {
                $orderItem->setRelatedOrder(null);
            }
        }

        return $this;
    }

    public function getRelatedTable(): ?Table
    {
        return $this->relatedTable;
    }

    public function setRelatedTable(Table $relatedTable): self
    {
        $this->relatedTable = $relatedTable;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
