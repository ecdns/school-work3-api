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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'orderForms')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orderForms')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'orderForms')]
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

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'orderForms')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: SellProcess::class, inversedBy: 'orderForms')]
    #[ORM\JoinColumn(name: 'sell_process_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SellProcess $sellProcess;

    // products
    #[ORM\OneToMany(mappedBy: 'orderForm', targetEntity: Product::class)]
    private ReadableCollection $orderFormProducts;

    public function __construct(string $name, string $description, DateTime $createdAt, SellProcess $order)
    {
        $this->name = $name;
        $this->description = $description;
        $this->sellProcess = $order;
        $this->project = $this->sellProcess->getProject();
        $this->company = $this->sellProcess->getCompany();
        $this->user = $this->sellProcess->getUser();
        $this->customer = $this->sellProcess->getCustomer();
        $this->totalExcludingTax = $this->sellProcess->getTotalExcludingTax();
        $this->totalIncludingTax = $this->sellProcess->getTotalIncludingTax();
        $this->createdAt = $createdAt;
        $this->orderFormProducts = $this->sellProcess->getOrderLines()->map(fn($orderLine) => $orderLine->getProduct());
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

    public function getSellProcess(): SellProcess
    {
        return $this->sellProcess;
    }

    public function setSellProcess(SellProcess $sellProcess): void
    {
        $this->sellProcess = $sellProcess;
    }

    public function getOrderFormProducts(): ReadableCollection
    {
        return $this->orderFormProducts;
    }

    public function setOrderFormProducts(Collection $orderFormProducts): void
    {
        $this->orderFormProducts = $orderFormProducts;
    }

    public function addOrderFormProduct(Product $orderFormProduct): void
    {
        $this->orderFormProducts->add($orderFormProduct);
    }

    public function removeOrderFormProduct(Product $orderFormProduct): void
    {
        $this->orderFormProducts->removeElement($orderFormProduct);
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
            'project' => $this->project->toArray(),
            'sellProcess' => $this->sellProcess->toArray(),
            'orderFormProducts' => $this->orderFormProducts->map(fn($orderFormProduct) => $orderFormProduct->toArray())->toArray(),
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
            'sellProcess' => $this->sellProcess->toFullArray(),
            'orderFormProducts' => $this->orderFormProducts->map(fn($orderFormProduct) => $orderFormProduct->toFullArray())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}