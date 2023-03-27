<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\HasLifecycleCallbacks]
class User implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $firstName;

    #[ORM\Column(type: 'string')]
    private string $lastName;

    #[ORM\Column(type: 'string', unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\Column(type: 'string')]
    private string $job;

    #[ORM\Column(type: 'string')]
    private string $phone;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $passwordConfirmedAt = null;

    // a user can have one user settings and one user settings can have one user
    #[ORM\OneToOne(targetEntity: UserSettings::class, cascade: ['persist', 'remove'])]
    private UserSettings $userSettings;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'users')]
    #[ORM\JoinTable(name: 'task_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'task_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(onDelete: 'CASCADE')]
    private Collection $tasks;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'users')]
    #[ORM\JoinTable(name: 'project_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(onDelete: 'CASCADE')]
    private Collection $projects;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Estimate::class)]
    private Collection $estimates;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: OrderForm::class)]
    private Collection $orderForms;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $Messages;

    #[ORM\Column(type: 'boolean')]
    private bool $isEnabled;

    public function __construct(string $firstName, string $lastName, string $email, string $password, Role $role, string $job, string $phone, Company $company, DateTime $createdAt, bool $isEnabled)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->job = $job;
        $this->phone = $phone;
        $this->company = $company;
        $this->isEnabled = $this->company->getIsEnabled();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getJob(): string
    {
        return $this->job;
    }

    public function setJob(string $job): void
    {
        $this->job = $job;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
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

    public function getPasswordConfirmedAt(): DateTime|null
    {
        return $this->passwordConfirmedAt;
    }

    public function setPasswordConfirmedAt(DateTime $passwordConfirmedAt): void
    {
        $this->passwordConfirmedAt = $passwordConfirmedAt;
    }

    public function getUserSettings(): UserSettings
    {
        return $this->userSettings;
    }

    public function setUserSettings(UserSettings $userSettings): void
    {
        $this->userSettings = $userSettings;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function setOrders(Collection $orders): void
    {
        $this->orders = $orders;
    }

    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function setCustomers(Collection $customers): void
    {
        $this->customers = $customers;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function setProjects(Collection $projects): void
    {
        $this->projects = $projects;
    }

    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function setInvoices(Collection $invoices): void
    {
        $this->invoices = $invoices;
    }

    public function getEstimates(): Collection
    {
        return $this->estimates;
    }

    public function setEstimates(Collection $estimates): void
    {
        $this->estimates = $estimates;
    }

    public function getOrderForms(): Collection
    {
        return $this->orderForms;
    }

    public function setOrderForms(Collection $orderForms): void
    {
        $this->orderForms = $orderForms;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getMessages(): Collection
    {
        return $this->Messages;
    }

    public function setMessages(Collection $Messages): void
    {
        $this->Messages = $Messages;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'role' => $this->getRole()->toArray(),
            'job' => $this->getJob(),
            'phone' => $this->getPhone(),
            'company' => $this->getCompany()->toArray(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'passwordConfirmedAt' => $this->getPasswordConfirmedAt()?->format('Y-m-d H:i:s'),
            'isEnabled' => $this->getIsEnabled(),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'role' => $this->getRole()->toArray(),
            'job' => $this->getJob(),
            'phone' => $this->getPhone(),
            'company' => $this->getCompany()->toArray(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'passwordConfirmedAt' => $this->getPasswordConfirmedAt()?->format('Y-m-d H:i:s'),
            'isEnabled' => $this->getIsEnabled(),
            'userSettings' => $this->getUserSettings()->toArray(),
            'orders' => $this->getOrders()->map(fn(Order $order) => $order->toArray())->toArray(),
            'customers' => $this->getCustomers()->map(fn(Customer $customer) => $customer->toArray())->toArray(),
            'tasks' => $this->getTasks()->map(fn(Task $task) => $task->toArray())->toArray(),
            'projects' => $this->getProjects()->map(fn(Project $project) => $project->toArray())->toArray(),
            'invoices' => $this->getInvoices()->map(fn(Invoice $invoice) => $invoice->toArray())->toArray(),
            'estimates' => $this->getEstimates()->map(fn(Estimate $estimate) => $estimate->toArray())->toArray(),
            'orderForms' => $this->getOrderForms()->map(fn(OrderForm $orderForm) => $orderForm->toArray())->toArray(),
            'messages' => $this->getMessages()->map(fn(Message $message) => $message->toArray())->toArray(),
        ];
    }

    public function __toString(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}






