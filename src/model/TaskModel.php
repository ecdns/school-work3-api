<?php

namespace Model;

class TaskModel implements ModelInterface
{
    public int $id;
    public string $title;
    public string $description;
    public string $location;
    public string $dueDate;
    public string $createdAt;
    public string $updatedAt;
    public int $projectId;
    public int $taskStatusId;
    public int $userId;

    public function __construct(
        int $id,
        string $title,
        string $description,
        string $location,
        string $dueDate,
        string $createdAt,
        string $updatedAt,
        int $projectId,
        int $taskStatusId,
        int $userId
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->location = $location;
        $this->dueDate = $dueDate;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->projectId = $projectId;
        $this->taskStatusId = $taskStatusId;
        $this->userId = $userId;
    }

    public function getTaskStatus(): string
    {
        switch ($this->taskStatusId) {
            case 1:
                return 'Not started';
            case 2:
                return 'In progress';
            case 3:
                return 'Completed';
            default:
                return 'Unknown';
        }
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

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    public function setDueDate(string $dueDate): void
    {
        $this->dueDate = $dueDate;
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

    public function getTaskStatusId(): int
    {
        return $this->taskStatusId;
    }

    public function setTaskStatusId(int $taskStatusId): void
    {
        $this->taskStatusId = $taskStatusId;
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
            $data['title'],
            $data['description'],
            $data['location'],
            $data['dueDate'],
            $data['createdAt'],
            $data['updatedAt'],
            $data['projectId'],
            $data['taskStatusId'],
            $data['userId']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'dueDate' => $this->dueDate,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'projectId' => $this->projectId,
            'taskStatusId' => $this->taskStatusId,
            'userId' => $this->userId,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }


}