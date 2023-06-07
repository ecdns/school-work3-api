<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_product')]
#[ORM\HasLifecycleCallbacks]
class InvoiceProduct implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'invoiceProducts')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Invoice $invoice;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'invoiceProduct')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    //constructor
    public function __construct(Invoice $invoice, Product $product, int $quantity)
    {
        $this->invoice = $invoice;
        $this->product = $product;
        $this->quantity = $quantity;
    }

    //getters
    public function getInvoice(): Invoice
    {
        return $this->invoice;
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

    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
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

    public function getTotalAmount(): float
    {
        if ($this->getProduct()->getIsDiscount()) {
            return $this->getProduct()->getSellPriceWithVatAndDiscount() * $this->getQuantity();
        } else {
            return $this->getProduct()->getSellPriceWithVat() * $this->getQuantity();
        }
    }

    public function getTotalAmountWithoutVat(): float
    {
        if ($this->getProduct()->getIsDiscount()) {
            return $this->getProduct()->getSellPriceWithDiscount() * $this->getQuantity();
        } else {
            return $this->getProduct()->getSellPrice() * $this->getQuantity();
        }
    }

    public function getTotalBuyPrice(): float
    {
        return $this->getProduct()->getBuyPriceWithVat() * $this->getQuantity();
    }

    public function getTotalBuyPriceWithoutVat(): float
    {
        return $this->getProduct()->getBuyPrice() * $this->getQuantity();
    }

    //to array
    public function toArray(): array
    {
        return [
            'invoice' => $this->getInvoice()->getId(),
            'product' => $this->getProduct()->toArray(),
            'quantity' => $this->getQuantity()
        ];
    }
}