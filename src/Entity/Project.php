<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'project')]
#[ORM\HasLifecycleCallbacks]
class Project implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'projects')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Customer::class, inversedBy: 'projects')]
    private Customer $customer;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\ManyToOne(targetEntity: ProjectStatus::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'project_status', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ProjectStatus $projectStatus;

    public function __construct(string $title, string $description, Company $company, DateTime $createdAt, Customer $customer, ProjectStatus $projectStatus)
    {
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->company = $company;
        $this->customer = $customer;
        $this->projectStatus = $projectStatus;
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

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function setOrders(Collection $orders): void
    {
        $this->orders = $orders;
    }

    public function getProjectStatus(): ProjectStatus
    {
        return $this->projectStatus;
    }

    public function setProjectStatus(ProjectStatus $projectStatus): void
    {
        $this->projectStatus = $projectStatus;
    }

    public function addTask(Task $task): void
    {
        $this->tasks->add($task);
    }

    public function removeTask(Task $task): void
    {
        $this->tasks->removeElement($task);
    }

    public function addOrder(Order $order): void
    {
        $this->orders->add($order);
    }

    public function removeOrder(Order $order): void
    {
        $this->orders->removeElement($order);
    }

    public function addUser(User $user): void
    {
        $this->users->add($user);
    }

    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
    }

    public function addUsers(Collection $users): void
    {
        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

    public function removeUsers(Collection $users): void
    {
        foreach ($users as $user) {
            $this->removeUser($user);
        }
    }

    public function addOrders(Collection $orders): void
    {
        foreach ($orders as $order) {
            $this->addOrder($order);
        }
    }

    public function removeOrders(Collection $orders): void
    {
        foreach ($orders as $order) {
            $this->removeOrder($order);
        }
    }

    public function addTasks(Collection $tasks): void
    {
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
    }

    public function removeTasks(Collection $tasks): void
    {
        foreach ($tasks as $task) {
            $this->removeTask($task);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'company' => $this->company->toArray()
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'company' => $this->company->toArray(),
            'users' => $this->users->map(fn (User $user) => $user->toArray())->toArray(),
            'customer' => $this->customer->toArray(),
            'tasks' => $this->tasks->map(fn (Task $task) => $task->toArray())->toArray(),
            'orders' => $this->orders->map(fn (Order $order) => $order->toArray())->toArray(),
            'projectStatus' => $this->projectStatus->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}