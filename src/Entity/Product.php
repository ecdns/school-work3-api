<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[ORM\HasLifecycleCallbacks]
class Product implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'float')]
    private float $buyPrice;

    #[ORM\Column(type: 'float')]
    private float $sellPrice;

    #[ORM\Column(type: 'float')]
    private float $quantity;

    #[ORM\Column(type: 'float')]
    private float $discount;

    #[ORM\Column(type: 'boolean')]
    private bool $isDiscount;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: ProductFamily::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'product_family', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ProductFamily $productFamily;

    #[ORM\ManyToOne(targetEntity: Vat::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'vat_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Vat $vat;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: QuantityUnit::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'quantity_unit', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private QuantityUnit $quantityUnit;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Supplier $supplier;

    // a product can be in many order lines (one-to-many)
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderLine::class)]
    private Collection $orderLines;

    public function __construct(
        string $name,
        string $description,
        float $buyPrice,
        float $sellPrice,
        float $quantity,
        float $discount,
        bool $isDiscount,
        ProductFamily $productFamily,
        Vat $vat,
        Company $company,
        QuantityUnit $quantityUnit,
        Supplier $supplier
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->quantity = $quantity;
        $this->discount = $discount;
        $this->isDiscount = $isDiscount;
        $this->productFamily = $productFamily;
        $this->vat = $vat;
        $this->company = $company;
        $this->quantityUnit = $quantityUnit;
        $this->supplier = $supplier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getBuyPrice(): float
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(float $buyPrice): void
    {
        $this->buyPrice = $buyPrice;
    }

    public function getSellPrice(): float
    {
        return $this->sellPrice;
    }

    public function setSellPrice(float $sellPrice): void
    {
        $this->sellPrice = $sellPrice;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getIsDiscount(): bool
    {
        return $this->isDiscount;
    }

    public function setIsDiscount(bool $isDiscount): void
    {
        $this->isDiscount = $isDiscount;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    public function getProductFamily(): ProductFamily
    {
        return $this->productFamily;
    }

    public function setProductFamily(ProductFamily $productFamily): void
    {
        $this->productFamily = $productFamily;
    }

    public function getVat(): Vat
    {
        return $this->vat;
    }

    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getQuantityUnit(): QuantityUnit
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(QuantityUnit $quantityUnit): void
    {
        $this->quantityUnit = $quantityUnit;
    }

    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    public function setOrderLines(Collection $orderLines): void
    {
        $this->orderLines = $orderLines;
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getSellPriceWithVat(): float
    {
        return $this->sellPrice * (1 + $this->vat->getRate() / 100);
    }

    public function getBuyPriceWithVat(): float
    {
        return $this->buyPrice * (1 + $this->vat->getRate() / 100);
    }

    public function getSellPriceWithDiscount(): float
    {
        return $this->sellPrice * (1 - $this->discount / 100);
    }

    public function getSellPriceWithVatAndDiscount(): float
    {
        return $this->getSellPriceWithVat() * (1 - $this->discount / 100);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'buyPrice' => $this->buyPrice,
            'sellPrice' => $this->sellPrice,
            'quantity' => $this->quantity,
            'discount' => $this->discount,
            'isDiscount' => $this->isDiscount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'productFamily' => $this->productFamily->toArray(),
            'vat' => $this->vat->toArray(),
            'company' => $this->company->toArray(),
            'quantityUnit' => $this->quantityUnit->toArray(),
            'supplier' => $this->supplier->toArray(),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'buyPrice' => $this->buyPrice,
            'sellPrice' => $this->sellPrice,
            'quantity' => $this->quantity,
            'discount' => $this->discount,
            'isDiscount' => $this->isDiscount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'productFamily' => $this->productFamily->toArray(),
            'vat' => $this->vat->toArray(),
            'company' => $this->company->toArray(),
            'quantityUnit' => $this->quantityUnit->toArray(),
            'supplier' => $this->supplier->toArray(),
            'orderLines' => $this->orderLines->map(fn (OrderLine $orderLine) => $orderLine->toArray())->toArray(),
        ];
    }

    public function toFullArrayWithVat(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'buyPrice' => $this->buyPrice,
            'sellPrice' => $this->sellPrice,
            'quantity' => $this->quantity,
            'discount' => $this->discount,
            'isDiscount' => $this->isDiscount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'productFamily' => $this->productFamily->toArray(),
            'vat' => $this->vat->toArray(),
            'company' => $this->company->toArray(),
            'quantityUnit' => $this->quantityUnit->toArray(),
            'supplier' => $this->supplier->toArray(),
            'orderLines' => $this->orderLines->map(fn (OrderLine $orderLine) => $orderLine->toArray())->toArray(),
            'sellPriceWithVat' => $this->getSellPriceWithVat(),
            'buyPriceWithVat' => $this->getBuyPriceWithVat(),
            'sellPriceWithDiscount' => $this->getSellPriceWithDiscount(),
            'sellPriceWithVatAndDiscount' => $this->getSellPriceWithVatAndDiscount(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}