<?php

namespace Model\setting;

use Model\ModelInterface;

class CompanySettingsModel implements ModelInterface
{
    private int $id;
    private string $primaryColor;
    private string $secondaryColor;
    private string $tertiaryColor;
    private string $createdAt;
    private string $updatedAt;
    private int $companyId;

    public function __construct(int $id, string $primaryColor, string $secondaryColor, string $tertiaryColor, string $createdAt, string $updatedAt, int $companyId)
    {
        $this->id = $id;
        $this->primaryColor = $primaryColor;
        $this->secondaryColor = $secondaryColor;
        $this->tertiaryColor = $tertiaryColor;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->companyId = $companyId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function getSecondaryColor(): string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(string $secondaryColor): void
    {
        $this->secondaryColor = $secondaryColor;
    }

    public function getTertiaryColor(): string
    {
        return $this->tertiaryColor;
    }

    public function setTertiaryColor(string $tertiaryColor): void
    {
        $this->tertiaryColor = $tertiaryColor;
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

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['primary_color'],
            $data['secondary_color'],
            $data['tertiary_color'],
            $data['created_at'],
            $data['updated_at'],
            $data['company_id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'tertiary_color' => $this->tertiaryColor,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'company_id' => $this->companyId
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}