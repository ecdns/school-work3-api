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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Customer $customer;

    #[ORM\Column(type: 'float')]
    private float $totalExcludingTax;

    #[ORM\Column(type: 'float')]
    private float $totalIncludingTax;

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

    #[ORM\ManyToOne(targetEntity: SellProcess::class, inversedBy: 'estimates')]
    #[ORM\JoinColumn(name: 'sell_process_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SellProcess $sellProcess;

    #[ORM\OneToMany(mappedBy: 'estimate', targetEntity: Product::class)]
    private ReadableCollection $estimateProducts;

    public function __construct(string $name, string $description, DateTime $createdAt, EstimateStatus $estimateStatus, SellProcess $sellProcess)
    {
        $this->name = $name;
        $this->description = $description;
        $this->sellProcess = $sellProcess;
        $this->project = $this->sellProcess->getProject();
        $this->company = $this->sellProcess->getCompany();
        $this->user = $this->sellProcess->getUser();
        $this->customer = $this->sellProcess->getCustomer();
        $this->totalExcludingTax = $this->sellProcess->getTotalExcludingTax();
        $this->totalIncludingTax = $this->sellProcess->getTotalIncludingTax();
        $this->createdAt = $createdAt;
        $this->estimateStatus = $estimateStatus;
        $this->estimateProducts = $this->sellProcess->getOrderLines()->map(fn($orderLine) => $orderLine->getProduct());
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

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(DateTime|null $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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

    public function getSellProcess(): SellProcess
    {
        return $this->sellProcess;
    }

    public function setSellProcess(SellProcess $sellProcess): void
    {
        $this->sellProcess = $sellProcess;
    }

    public function getEstimateProducts(): ReadableCollection
    {
        return $this->estimateProducts;
    }

    public function setEstimateProducts(ReadableCollection $estimateProducts): void
    {
        $this->estimateProducts = $estimateProducts;
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
            'company' => $this->company->toArray(),
            'user' => $this->user->toArray(),
            'customer' => $this->customer->toArray(),
            'totalExcludingTax' => $this->totalExcludingTax,
            'totalIncludingTax' => $this->totalIncludingTax,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'expiredAt' => $this->expiredAt?->format('Y-m-d H:i:s'),
            'estimateStatus' => $this->estimateStatus->toArray(),
            'project' => $this->project->toArray(),
            'sellProcess' => $this->sellProcess->toArray(),
            'estimateProducts' => $this->estimateProducts->map(fn($estimateProduct) => $estimateProduct->toArray())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}