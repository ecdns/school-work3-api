<?php

namespace Model\document;

use Model\ModelInterface;

class ContractModel implements ModelInterface
{
    private int $id;
    private string $name;
    private string $description;
    private float $totalExcludingTax;
    private float $totalIncludingTax;
    private string $createdAt;
    private string $updatedAt;
    private int $projectId;
    private int $vatId;
    private int $companyId;
    private int $userId;
    private int $customerId;
    private int $contractTypeId;

    public function __construct(int $id, string $name, string $description, float $totalExcludingTax, float $totalIncludingTax, string $createdAt, string $updatedAt, int $projectId, int $vatId, int $companyId, int $userId, int $customerId, int $contractTypeId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->totalExcludingTax = $totalExcludingTax;
        $this->totalIncludingTax = $totalIncludingTax;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->projectId = $projectId;
        $this->vatId = $vatId;
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->customerId = $customerId;
        $this->contractTypeId = $contractTypeId;
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

    public function getTotalExcludingTax(): float
    {
        return $this->totalExcludingTax;
    }

    public function setTotalExcludingTax(float $totalExcludingTax): void
    {
        $this->totalExcludingTax = $totalExcludingTax;
    }

    public function getTotalIncludingTax(): float
    {
        return $this->totalIncludingTax;
    }

    public function setTotalIncludingTax(float $totalIncludingTax): void
    {
        $this->totalIncludingTax = $totalIncludingTax;
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

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function setProjectId(int $projectId): void
    {
        $this->projectId = $projectId;
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getContractTypeId(): int
    {
        return $this->contractTypeId;
    }

    public function setContractTypeId(int $contractTypeId): void
    {
        $this->contractTypeId = $contractTypeId;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'],
            $data['total_excluding_tax'],
            $data['total_including_tax'],
            $data['created_at'],
            $data['updated_at'],
            $data['project_id'],
            $data['vat_id'],
            $data['company_id'],
            $data['user_id'],
            $data['customer_id'],
            $data['contract_type_id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'total_excluding_tax' => $this->totalExcludingTax,
            'total_including_tax' => $this->totalIncludingTax,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'project_id' => $this->projectId,
            'vat_id' => $this->vatId,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'customer_id' => $this->customerId,
            'contract_type_id' => $this->contractTypeId
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}