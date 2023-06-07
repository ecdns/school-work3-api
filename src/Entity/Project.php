<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projectsOwned')]
    #[ORM\JoinColumn(name: 'creator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $creator;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'projects')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Customer $customer;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\ManyToOne(targetEntity: ProjectStatus::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'project_status', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ProjectStatus $projectStatus;

    //oneToMany for estimate
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Estimate::class)]
    private Collection $estimates;

    //OneToMany for invoice
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Invoice::class)]
    private Collection $invoices;

    //OneToMany for orderForm
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: OrderForm::class)]
    private Collection $orderForms;

    //OneToMany for Message
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct(string $name, string $description, Company $company, User $creator , Customer $customer, ProjectStatus $projectStatus)
    {
        $this->name = $name;
        $this->description = $description;
        $this->company = $company;
        $this->creator = $creator;
        $this->customer = $customer;
        $this->projectStatus = $projectStatus;
        $this->users = new ArrayCollection();
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


    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
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

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): void
    {
        $this->creator = $creator;
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

    public function addEstimate(Estimate $estimate): void
    {
        $this->estimates->add($estimate);
    }

    public function removeEstimate(Estimate $estimate): void
    {
        $this->estimates->removeElement($estimate);
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
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'company' => $this->company->toArray(),
            'creator' => $this->creator->toArray(),
            'customer' => $this->customer->toArray(),
            'users' => $this->users->map(fn (User $user) => $user->toArray())->toArray(),
            'projectStatus' => $this->projectStatus->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}