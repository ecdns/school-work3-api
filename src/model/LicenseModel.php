<?php

namespace Model;

class LicenseModel implements ModelInterface
{
    private int $id;
    private string $name;
    private string $description;
    private float $price;
    private int $maxUsers;
    private int $validityPeriod;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $id, string $name, string $description, float $price, int $maxUsers, int $validityPeriod, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->maxUsers = $maxUsers;
        $this->validityPeriod = $validityPeriod;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getMaxUsers(): int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function getValidityPeriod(): int
    {
        return $this->validityPeriod;
    }

    public function setValidityPeriod(int $validityPeriod): void
    {
        $this->validityPeriod = $validityPeriod;
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

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['maxUsers'],
            $data['validityPeriod'],
            $data['createdAt'],
            $data['updatedAt']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'maxUsers' => $this->maxUsers,
            'validityPeriod' => $this->validityPeriod,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}