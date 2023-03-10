<?php

namespace Model\type;

use Model\ModelInterface;

class QuantityUnitModel implements ModelInterface
{
    private int $id;
    private string $unit;
    private string $description;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $id, string $unit, string $description, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->unit = $unit;
        $this->description = $description;
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

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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
            $data['unit'],
            $data['description'],
            $data['created_at'],
            $data['updated_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'unit' => $this->unit,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}