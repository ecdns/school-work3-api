<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'task')]
#[ORM\HasLifecycleCallbacks]
class Task implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    public int $id;

    #[ORM\Column(type: 'string')]
    public string $title;

    #[ORM\Column(type: 'string')]
    public string $description;

    #[ORM\Column(type: 'string')]
    public string $location;

    #[ORM\Column(type: 'string')]
    public string $dueDate;

    #[ORM\Column(type: 'datetime', nullable: false)]
    public DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public DateTime|null $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskStatus::class)]
    public TaskStatus $taskStatuses;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    private User $users;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    public Project $project;

    public function __construct(string $title, string $description, string $location, string $dueDate, Project $project, User $users)
    {
        $this->title = $title;
        $this->description = $description;
        $this->location = $location;
        $this->dueDate = $dueDate;
        $this->project = $project;
        $this->users = $users;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    public function getTaskStatuses(): TaskStatus
    {
        return $this->taskStatuses;
    }

    public function getUsers(): User
    {
        return $this->users;
    }


    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function addTaskStatus(TaskStatus $taskStatus): void
    {
        $this->taskStatuses = $taskStatus;
    }

    public function setUser(User $user): void
    {
        $this->users = $user;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'dueDate' => $this->dueDate,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'dueDate' => $this->dueDate,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'taskStatuses' => $this->taskStatuses->toArray(),
            'users' => $this->users->toArray(),
            'project' => $this->project->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}