<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice')]
#[ORM\HasLifecycleCallbacks]
class Invoice implements EntityInterface
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

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;


    // many to many product
    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceProduct::class)]
    private Collection $invoiceProducts;

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

    public function getInvoiceProducts(): Collection
    {
        return $this->invoiceProducts;
    }

    //add product to estimate
    public function addInvoiceProduct(InvoiceProduct $product): void
    {
        $this->invoiceProducts->add($product);
    }

    //remove product from estimate
    public function removeInvoiceProduct(InvoiceProduct $product): void
    {
        $this->invoiceProducts->removeElement($product);
    }

    public function getTotalAmount(): float
    {
        $total = 0;
        foreach ($this->invoiceProducts as $invoiceProduct) {
            $total += $invoiceProduct->getTotalAmountWithoutVat();
        }
        return $total;
    }

    public function getTotalAmountWithVat(): float
    {
        $total = 0;
        foreach ($this->invoiceProducts as $invoiceProduct) {
            $total += $invoiceProduct->getTotalAmount();
        }
        return $total;
    }

    public function getTotalBuyPrice(): float
    {
        $total = 0;
        foreach ($this->invoiceProducts as $invoiceProduct) {
            $total += $invoiceProduct->getTotalBuyPriceWithoutVat();
        }
        return $total;
    }

    public function getTotalBuyPriceWithVat(): float
    {
        $total = 0;
        foreach ($this->invoiceProducts as $invoiceProduct) {
            $total += $invoiceProduct->getTotalBuyPrice();
        }
        return $total;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'totalAmount' => $this->getTotalAmount(),
            'totalAmountWithVat' => $this->getTotalAmountWithVat(),
            'totalBuyPrice' => $this->getTotalBuyPrice(),
            'totalBuyPriceWithVat' => $this->getTotalBuyPriceWithVat(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'project' => $this->project->toArray(),
            'invoiceProducts' => $this->invoiceProducts->map(fn($invoiceProduct) => $invoiceProduct->toArray())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}