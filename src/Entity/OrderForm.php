<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_form')]
#[ORM\HasLifecycleCallbacks]
class OrderForm implements EntityInterface
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

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'orderForms')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;

    // many to many product
    #[ORM\OneToMany(mappedBy: 'orderForm', targetEntity: OrderFormProduct::class)]
    private Collection $orderFormProducts;


    public function __construct(string $name, string $description, Project $project)
    {
        $this->name = $name;
        $this->description = $description;
        $this->project = $project;

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


    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getOrderFormProducts(): Collection
    {
        return $this->orderFormProducts;
    }

    //add product to orderForm
    public function addOrderFormProduct(OrderFormProduct $product): void
    {
        $this->orderFormProducts->add($product);
    }

    //remove product from orderForm
    public function removeOrderFormProduct(OrderFormProduct $product): void
    {
        $this->orderFormProducts->removeElement($product);
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
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'project' => $this->project->toArray(),
            'orderFormProducts' => $this->orderFormProducts->map(fn($orderFormProduct) => $orderFormProduct->toArray())->toArray(),
        ];
    }


    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}