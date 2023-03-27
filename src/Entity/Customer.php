<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer')]
#[ORM\HasLifecycleCallbacks]
class Customer implements EntityInterface
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
    private string $address;

    #[ORM\Column(type: 'string')]
    private string $city;

    #[ORM\Column(type: 'string')]
    private string $country;

    #[ORM\Column(type: 'string')]
    private string $zipCode;

    #[ORM\Column(type: 'string')]
    private string $phone;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $customerCompanyName;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'customers')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customers')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: CustomerStatus::class, inversedBy: 'customers')]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CustomerStatus $status;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Estimate::class)]
    private Collection $estimates;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: OrderForm::class)]
    private Collection $orderForms;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Contract::class)]
    private Collection $contracts;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'customers')]
    #[ORM\JoinTable(name: 'customer_project')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $projects;

    public function __construct(string $firstName, string $lastName, string $email, string $address, string $city, string $country, string $zipCode, string $phone, string $customerCompanyName, Company $company, User $user, CustomerStatus $status)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->phone = $phone;
        $this->customerCompanyName = $customerCompanyName;
        $this->company = $company;
        $this->user = $user;
        $this->status = $status;
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

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
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

    public function getCustomerCompanyName(): string
    {
        return $this->customerCompanyName;
    }

    public function setCustomerCompanyName(string $customerCompanyName): void
    {
        $this->customerCompanyName = $customerCompanyName;
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

    public function getStatus(): CustomerStatus
    {
        return $this->status;
    }

    public function setStatus(CustomerStatus $status): void
    {
        $this->status = $status;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
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

    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function setContracts(Collection $contracts): void
    {
        $this->contracts = $contracts;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function setProjects(Collection $projects): void
    {
        $this->projects = $projects;
    }

    public function __toString(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function toArray() : array
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'address' => $this->getAddress(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'zipCode' => $this->getZipCode(),
            'phone' => $this->getPhone(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'customerCompanyName' => $this->getCustomerCompanyName(),
            'company' => $this->getCompany(),
            'user' => $this->getUser(),
            'status' => $this->getStatus(),
            'messages' => $this->getMessages(),
            'estimates' => $this->getEstimates(),
            'invoices' => $this->getInvoices(),
            'orderForms' => $this->getOrderForms(),
            'contracts' => $this->getContracts(),
            'projects' => $this->getProjects(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}