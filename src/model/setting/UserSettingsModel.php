<?php

namespace Model\setting;

use Model\ModelInterface;

class UserSettingsModel implements ModelInterface
{
    public int $id;
    public string $language;
    public string $createdAt;
    public string $updatedAt;
    public int $userId;

    public function __construct(int $id, string $language, string $createdAt, string $updatedAt, int $userId)
    {
        $this->id = $id;
        $this->language = $language;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['language'],
            $data['created_at'],
            $data['updated_at'],
            $data['user_id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'language' => $this->language,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user_id' => $this->userId
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}