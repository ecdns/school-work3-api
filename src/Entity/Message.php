<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'message')]
#[ORM\HasLifecycleCallbacks]
class Message implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    public int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    public User $sender;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    public Project $project;

    #[ORM\Column(type: 'string')]
    public string $message;

    #[ORM\Column(type: 'boolean')]
    public bool $isRead;

    #[ORM\Column(type: 'datetime', nullable: false)]
    public DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public DateTime|null $updatedAt = null;

    public function __construct(User $sender, Project $project, string $message, bool $isRead, bool $isLiked)
    {
        $this->sender = $sender;
        $this->project = $project;
        $this->message = $message;
        $this->isRead = $isRead;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    public function setSender(User $sender): void
    {
        $this->sender = $sender;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->sender->toArray(),
            'project' => $this->project->toArray(),
            'message' => $this->message,
            'isRead' => $this->isRead,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}