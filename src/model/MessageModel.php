<?php

namespace Model;

class MessageModel implements ModelInterface
{
    public int $id;
    public int $senderId;
    public int $receiverId;
    public string $message;
    public bool $isRead;
    public bool $isLiked;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(int $id, int $senderId, int $receiverId, string $message, bool $isRead, bool $isLiked, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->message = $message;
        $this->isRead = $isRead;
        $this->isLiked = $isLiked;
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

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function setSenderId(int $senderId): void
    {
        $this->senderId = $senderId;
    }

    public function getReceiverId(): int
    {
        return $this->receiverId;
    }

    public function setReceiverId(int $receiverId): void
    {
        $this->receiverId = $receiverId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getIsLiked(): bool
    {
        return $this->isLiked;
    }

    public function setIsLiked(bool $isLiked): void
    {
        $this->isLiked = $isLiked;
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
            $data['sender_id'],
            $data['receiver_id'],
            $data['message'],
            $data['is_read'],
            $data['is_liked'],
            $data['created_at'],
            $data['updated_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'message' => $this->message,
            'is_read' => $this->isRead,
            'is_liked' => $this->isLiked,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}