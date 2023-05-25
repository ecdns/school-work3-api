<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'estimate_product')]
#[ORM\HasLifecycleCallbacks]
class EstimateProduct implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Estimate::class, inversedBy: 'estimateProduct')]
    #[ORM\JoinColumn(name: 'estimate_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'estimateProduct')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    //constructor
    public function __construct(Estimate $estimate, Product $product, int $quantity)
    {
        $this->estimate = $estimate;
        $this->product = $product;
        $this->quantity = $quantity;
    }

    //getters
    public function getEstimate(): Estimate
    {
        return $this->estimate;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    //setters

    public function setEstimate(Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }


    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    //to array
    public function toArray(): array
    {
        return [
            'estimate' => $this->getEstimate()->toArray(),
            'product' => $this->getProduct()->toArray(),
            'quantity' => $this->getQuantity()
        ];
    }
}