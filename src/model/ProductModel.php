<?php

namespace Model;

class ProductModel implements ModelInterface
{
    private int $id;
    private string $name;
    private string $description;
    private float $buyPrice;
    private float $sellPrice;
    private float $quantity;
    private float $discount;
    private string $createdAt;
    private string $updatedAt;
    private int $productFamilyId;
    private int $vatId;
    private int $companyId;
    private int $quantityUnitId;
    private int $supplierId;

    public function __construct(int $id, string $name, string $description, float $buyPrice, float $sellPrice, float $quantity, float $discount, string $createdAt, string $updatedAt, int $productFamilyId, int $vatId, int $companyId, int $quantityUnitId, int $supplierId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->quantity = $quantity;
        $this->discount = $discount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->productFamilyId = $productFamilyId;
        $this->vatId = $vatId;
        $this->companyId = $companyId;
        $this->quantityUnitId = $quantityUnitId;
        $this->supplierId = $supplierId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getProductFamilyId(): int
    {
        return $this->productFamilyId;
    }

    public function setProductFamilyId(int $productFamilyId): void
    {
        $this->productFamilyId = $productFamilyId;
    }

    public function getVatId(): int
    {
        return $this->vatId;
    }

    public function setVatId(int $vatId): void
    {
        $this->vatId = $vatId;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function getQuantityUnitId(): int
    {
        return $this->quantityUnitId;
    }

    public function setQuantityUnitId(int $quantityUnitId): void
    {
        $this->quantityUnitId = $quantityUnitId;
    }

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }

    public function setSupplierId(int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'],
            $data['buy_price'],
            $data['sell_price'],
            $data['quantity'],
            $data['discount'],
            $data['created_at'],
            $data['updated_at'],
            $data['product_family_id'],
            $data['vat_id'],
            $data['company_id'],
            $data['quantity_unit_id'],
            $data['supplier_id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'buy_price' => $this->buyPrice,
            'sell_price' => $this->sellPrice,
            'quantity' => $this->quantity,
            'discount' => $this->discount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'product_family_id' => $this->productFamilyId,
            'vat_id' => $this->vatId,
            'company_id' => $this->companyId,
            'quantity_unit_id' => $this->quantityUnitId,
            'supplier_id' => $this->supplierId
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}