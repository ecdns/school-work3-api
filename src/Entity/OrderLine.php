<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_line')]
#[ORM\HasLifecycleCallbacks]
class OrderLine implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'float')]
    private float $totalIncludingTax;

    #[ORM\Column(type: 'float')]
    private float $totalExcludingTax;

    // order
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderLines')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Order $order;

    // product
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderLines')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    public function __construct(int $quantity, Order $order, Product $product)
    {
        $this->quantity = $quantity;
        $this->order = $order;
        $this->product = $product;
        $this->totalIncludingTax = $this->getTotalIncludingTax();
        $this->totalExcludingTax = $this->getTotalExcludingTax();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalIncludingTax(): float
    {
        // check if the product has a discount rate and if it is actually discounted
        if ($this->product->getDiscount() > 0 && $this->product->getIsDiscount()) {
            // if the product is discounted, calculate the discounted price
            $discountedPrice = $this->product->getSellPriceWithVatAndDiscount();
            // return the discounted price * quantity
            return $this->quantity * $discountedPrice;
        } else {
            // if the product is not discounted, return the normal price * quantity
            return $this->quantity * $this->product->getSellPriceWithVat();
        }
    }

    public function getTotalExcludingTax(): float
    {
        // check if the product has a discount rate and if it is actually discounted
        if ($this->product->getDiscount() > 0 && $this->product->getIsDiscount()) {
            // if the product is discounted, calculate the discounted price
            $discountedPrice = $this->product->getSellPriceWithDiscount();
            // return the discounted price * quantity
            return $this->quantity * $discountedPrice;
        } else {
            // if the product is not discounted, return the normal price * quantity
            return $this->quantity * $this->product->getSellPrice();
        }
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setTotalIncludingTax(float $totalIncludingTax): void
    {
        $this->totalIncludingTax = $totalIncludingTax;
    }

    public function setTotalExcludingTax(float $totalExcludingTax): void
    {
        $this->totalExcludingTax = $totalExcludingTax;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'totalIncludingTax' => $this->totalIncludingTax,
            'totalExcludingTax' => $this->totalExcludingTax,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'order' => $this->order->toArray(),
            'product' => $this->product->toArray(),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'totalIncludingTax' => $this->totalIncludingTax,
            'totalExcludingTax' => $this->totalExcludingTax,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'order' => $this->order->toFullArray(),
            'product' => $this->product->toFullArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}