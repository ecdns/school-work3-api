<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contract')]
#[ORM\HasLifecycleCallbacks]
class Contract implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'float')]
    private float $totalExcludingTax;

    #[ORM\Column(type: 'float')]
    private float $totalIncludingTax;

    #[ORM\Column(type: 'datetime',nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime',nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: Vat::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'vat_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private Vat $vat;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: ContractType::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(name: 'contract_type', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ContractType $contractType;

    public function __construct(string $name, string $description, float $totalExcludingTax, Project $project, Vat $vat, Company $company, User $user, Customer $customer, ContractType $contractType)
    {
        $this->name = $name;
        $this->description = $description;
        $this->totalExcludingTax = $totalExcludingTax;
        $this->totalIncludingTax = $totalExcludingTax * (1 + $vat->getRate() / 100);
        $this->project = $project;
        $this->vat = $vat;
        $this->company = $company;
        $this->user = $user;
        $this->customer = $customer;
        $this->contractType = $contractType;
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
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getVat(): Vat
    {
        return $this->vat;
    }

    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
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

    public function getContractType(): ContractType
    {
        return $this->contractType;
    }

    public function setContractType(ContractType $contractType): void
    {
        $this->contractType = $contractType;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'totalExcludingTax' => $this->totalExcludingTax,
            'totalIncludingTax' => $this->totalIncludingTax,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'project' => $this->project->toArray(),
            'vat' => $this->vat->toArray(),
            'company' => $this->company->toArray(),
            'user' => $this->user->toArray(),
            'customer' => $this->customer->toArray(),
            'contractType' => $this->contractType->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}