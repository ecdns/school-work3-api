<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'estimate')]
#[ORM\HasLifecycleCallbacks]
class Estimate implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $description;


    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $expiredAt = null;

    #[ORM\ManyToOne(targetEntity: EstimateStatus::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'estimate_status', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private EstimateStatus $estimateStatus;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;


    #[ORM\OneToMany(mappedBy: 'estimate', targetEntity: EstimateProduct::class)]
    private Collection $estimateProduct;

    public function __construct(string $name, string $description, Project $project, DateTime $expiredAt, EstimateStatus $estimateStatus)
    {
        $this->name = $name;
        $this->description = $description;
        $this->project = $project;
        $this->expiredAt = $expiredAt;
        $this->estimateStatus = $estimateStatus;
    }

    public function getId(): int
    {
        return $this->id;
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



    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getExpiredAt(): DateTime|null
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(DateTime|null $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    public function getEstimateStatus(): EstimateStatus
    {
        return $this->estimateStatus;
    }

    public function setEstimateStatus(EstimateStatus $estimateStatus): void
    {
        $this->estimateStatus = $estimateStatus;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }


    public function getEstimateProducts(): Collection
    {
        return $this->estimateProduct;
    }

    //add product to estimate
    public function addEstimateProduct(EstimateProduct $product): void
    {
        $this->estimateProduct->add($product);
    }

    //remove product from estimate
    public function removeEstimateProduct(EstimateProduct $product): void
    {
        $this->estimateProduct->removeElement($product);
    }


    public function __toString(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'project' => $this->project->toArray(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'expiredAt' => $this->expiredAt?->format('Y-m-d H:i:s'),
            'estimateStatus' => $this->estimateStatus->toArray(),
            'estimateProducts' => $this->estimateProduct->map(fn($estimateProduct) => $estimateProduct->toArray())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}