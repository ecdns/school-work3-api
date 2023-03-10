<?php

namespace Model;

class ProjectModel implements ModelInterface
{
    private int $id;
    private string $title;
    private string $description;
    private string $createdAt;
    private string $updatedAt;
    private string $expiredAt;
    private int $companyId;
    private int $userId;
    private int $customerId;

    public function __construct(int $id, string $title, string $description, string $createdAt, string $updatedAt, string $expiredAt, int $companyId, int $userId, int $customerId)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->expiredAt = $expiredAt;
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->customerId = $customerId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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

    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(string $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
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

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['created_at'],
            $data['updated_at'],
            $data['expired_at'],
            $data['company_id'],
            $data['user_id'],
            $data['customer_id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'expired_at' => $this->expiredAt,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'customer_id' => $this->customerId
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}