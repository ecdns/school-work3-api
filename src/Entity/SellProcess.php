<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sell_process')]
#[ORM\HasLifecycleCallbacks]
class SellProcess implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'sell_processes')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sell_processes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'sell_processes')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id',onDelete: 'CASCADE')]
    private Customer $customer;

    #[ORM\Column(type: 'float')]
    private float $totalExcludingTax;

    #[ORM\Column(type: 'float')]
    private float $totalIncludingTax;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'sell_processes')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\OneToMany(mappedBy: 'sell_process', targetEntity: OrderLine::class)]
    private Collection $orderLines;

    #[ORM\OneToMany(mappedBy: 'sell_process', targetEntity: OrderPayment::class)]
    private Collection $orderPayments;

    #[ORM\ManyToOne(targetEntity: SellProcessStatus::class, inversedBy: 'sell_processes')]
    #[ORM\JoinColumn(name: 'sell_process_status_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SellProcessStatus $sellProcessStatus;

    #[ORM\OneToMany(mappedBy: 'sell_process', targetEntity: Estimate::class)]
    private Collection $estimates;

    #[ORM\OneToMany(mappedBy: 'sell_process', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'sell_process', targetEntity: OrderForm::class)]
    private Collection $orderForms;

    public function __construct(string $name, string $description, User $user, DateTime $createdAt, Project $project)
    {
        $this->name = $name;
        $this->description = $description;
        $this->project = $project;
        $this->company = $project->getCompany();
        $this->user = $user;
        $this->customer = $project->getCustomer();
        $this->totalExcludingTax = $this->calculateTotalExcludingTax();
        $this->totalIncludingTax = $this->calculateTotalIncludingTax();
        $this->createdAt = $createdAt;
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

    public function setTotalExcludingTax(): void
    {
        $this->totalExcludingTax = $this->calculateTotalExcludingTax();
    }

    public function getTotalTax(): float
    {
        return $this->totalIncludingTax - $this->totalExcludingTax;
    }

    public function calculateTotalIncludingTax(): float
    {
        $totalIncludingTax = 0;
        if ($this->orderLines->count() > 0) {
            foreach ($this->orderLines as $orderLine) {
                $totalIncludingTax += $orderLine->getTotalIncludingTax();
            }
        } else {
            $totalIncludingTax = $this->totalIncludingTax;
        }

        return $totalIncludingTax;
    }

    public function calculateTotalExcludingTax(): float
    {
        $totalExcludingTax = 0;
        if ($this->orderLines->count() > 0) {
            foreach ($this->orderLines as $orderLine) {
                $totalExcludingTax += $orderLine->getTotalExcludingTax();
            }
        } else {
            $totalExcludingTax = $this->totalExcludingTax;
        }

        return $totalExcludingTax;
    }

    public function getTotalIncludingTax(): float
    {
        return $this->totalIncludingTax;
    }

    public function setTotalIncludingTax(): void
    {
        $this->totalIncludingTax = $this->calculateTotalIncludingTax();
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

    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    public function setOrderLines(Collection $orderLines): void
    {
        $this->orderLines = $orderLines;
    }

    public function getOrderPayments(): Collection
    {
        return $this->orderPayments;
    }

    public function setOrderPayments(Collection $orderPayments): void
    {
        $this->orderPayments = $orderPayments;
    }

    public function getSellProcessStatus(): SellProcessStatus
    {
        return $this->sellProcessStatus;
    }

    public function setSellProcessStatus(SellProcessStatus $sellProcessStatus): void
    {
        $this->sellProcessStatus = $sellProcessStatus;
    }

    public function getEstimates(): Collection
    {
        return $this->estimates;
    }

    public function setEstimates(Collection $estimates): void
    {
        $this->estimates = $estimates;
    }

    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function setInvoices(Collection $invoices): void
    {
        $this->invoices = $invoices;
    }

    public function getOrderForms(): Collection
    {
        return $this->orderForms;
    }

    public function setOrderForms(Collection $orderForms): void
    {
        $this->orderForms = $orderForms;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function addOrderLine(OrderLine $orderLine): void
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines->add($orderLine);
            $orderLine->setOrder($this);
        }
    }

    public function removeOrderLine(OrderLine $orderLine): void
    {
        if ($this->orderLines->contains($orderLine)) {
            $this->orderLines->removeElement($orderLine);
        }
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
        ];
    }

    public function toFullArray(): array
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
            'project' => $this->project->toArray(),
            'orderLines' => $this->orderLines->map(fn (OrderLine $orderLine) => $orderLine->toArray())->toArray(),
            'orderPayments' => $this->orderPayments->map(fn (OrderPayment $orderPayment) => $orderPayment->toArray())->toArray(),
            'sellProcessStatus' => $this->sellProcessStatus->toArray(),
            'estimates' => $this->estimates->map(fn (Estimate $estimate) => $estimate->toArray())->toArray(),
            'invoices' => $this->invoices->map(fn (Invoice $invoice) => $invoice->toArray())->toArray(),
            'orderForms' => $this->orderForms->map(fn (OrderForm $orderForm) => $orderForm->toArray())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}