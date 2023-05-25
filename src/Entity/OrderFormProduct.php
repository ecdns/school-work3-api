<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orderForm_product')]
#[ORM\HasLifecycleCallbacks]
class OrderFormProduct implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: OrderForm::class, inversedBy: 'orderFormProducts')]
    #[ORM\JoinColumn(name: 'orderForm_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private OrderForm $orderForm;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderFormProduct')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    //constructor
    public function __construct(OrderForm $orderForm, Product $product, int $quantity)
    {
        $this->orderForm = $orderForm;
        $this->product = $product;
        $this->quantity = $quantity;
    }

    //getters
    public function getOrderForm(): OrderForm
    {
        return $this->orderForm;
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

    public function setOrderForm(OrderForm $orderForm): void
    {
        $this->orderForm = $orderForm;
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
            'orderForm' => $this->getOrderForm()->toArray(),
            'product' => $this->getProduct()->toArray(),
            'quantity' => $this->getQuantity()
        ];
    }
}