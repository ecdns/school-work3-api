<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'company')]
#[ORM\HasLifecycleCallbacks]
class Company implements EntityInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

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

    #[ORM\Column(type: 'string')]
    private string $slogan;

    #[ORM\Column(type: 'string')]
    private string $logoPath;

    #[ORM\ManyToOne(targetEntity: License::class, inversedBy: 'companies')]
    #[ORM\JoinColumn(name: 'license_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private License $license;

    #[ORM\Column(type: 'datetime')]
    private DateTime $licenseExpirationDate;

    #[ORM\Column(type: 'string')]
    private string $language;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updated_at = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isEnabled;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Supplier::class)]
    private Collection $suppliers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Product::class)]
    private Collection $products;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: OrderForm::class)]
    private Collection $orderForms;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Estimate::class)]
    private Collection $estimates;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Contract::class)]
    private Collection $contracts;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Project::class)]
    private Collection $projects;

    #[ORM\OneToOne(targetEntity: CompanySettings::class, cascade: ['persist', 'remove'])]
    private CompanySettings $companySettings;

    public function __construct(
        string $name,
        string $address,
        string $city,
        string $country,
        string $zipCode,
        string $phone,
        string $slogan,
        string $logoPath,
        License $license,
        DateTime $licenseExpirationDate,
        string $language,
        bool $isEnabled
    ) {
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->phone = $phone;
        $this->slogan = $slogan;
        $this->logoPath = $logoPath;
        $this->license = $license;
        $this->licenseExpirationDate = $licenseExpirationDate;
        $this->language = $language;
        $this->isEnabled = $isEnabled;
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

    public function getSlogan(): string
    {
        return $this->slogan;
    }

    public function setSlogan(string $slogan): void
    {
        $this->slogan = $slogan;
    }

    public function getLogoPath(): string
    {
        return $this->logoPath;
    }

    public function setLogoPath(string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }

    public function getLicense(): License
    {
        return $this->license;
    }

    public function setLicense(License $license): void
    {
        $this->license = $license;
    }

    public function getLicenseExpirationDate(): DateTime
    {
        return $this->licenseExpirationDate;
    }

    public function setLicenseExpirationDate(DateTime $licenseExpirationDate): void
    {
        $this->licenseExpirationDate = $licenseExpirationDate;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    #[ORM\PrePersist]
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    #[ORM\PreUpdate]
    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime|null $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function setCustomers(Collection $customers): void
    {
        $this->customers = $customers;
    }

    public function getSuppliers(): Collection
    {
        return $this->suppliers;
    }

    public function setSuppliers(Collection $suppliers): void
    {
        $this->suppliers = $suppliers;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): void
    {
        $this->products = $products;
    }

    public function getOrderForms(): Collection
    {
        return $this->orderForms;
    }

    public function setOrderForms(Collection $orderForms): void
    {
        $this->orderForms = $orderForms;
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

    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function setContracts(Collection $contracts): void
    {
        $this->contracts = $contracts;
    }

    public function getCompanySettings(): CompanySettings
    {
        return $this->companySettings;
    }

    public function setCompanySettings(CompanySettings $companySettings): void
    {
        $this->companySettings = $companySettings;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function setProjects(Collection $projects): void
    {
        $this->projects = $projects;
    }



    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'address' => $this->getAddress(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'zipCode' => $this->getZipCode(),
            'phone' => $this->getPhone(),
            'slogan' => $this->getSlogan(),
            'logoPath' => $this->getLogoPath(),
            'license' => $this->getLicense(),
            'licenseExpirationDate' => $this->getLicenseExpirationDate(),
            'language' => $this->getLanguage(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'isEnabled' => $this->getIsEnabled(),
            'users' => $this->getUsers(),
            'customers' => $this->getCustomers(),
            'suppliers' => $this->getSuppliers(),
            'products' => $this->getProducts(),
            'orderForms' => $this->getOrderForms(),
            'invoices' => $this->getInvoices(),
            'estimates' => $this->getEstimates(),
            'contracts' => $this->getContracts(),
            'companySettings' => $this->getCompanySettings(),
            'projects' => $this->getProjects(),
        ];
    }

    public function toFullArrayWithUsers(): array
    {
        $users = [];
        foreach ($this->getUsers() as $user) {
            $users[] = $user->toArray();
        }
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'address' => $this->getAddress(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'zipCode' => $this->getZipCode(),
            'phone' => $this->getPhone(),
            'slogan' => $this->getSlogan(),
            'logoPath' => $this->getLogoPath(),
            'license' => $this->getLicense(),
            'licenseExpirationDate' => $this->getLicenseExpirationDate(),
            'language' => $this->getLanguage(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'isEnabled' => $this->getIsEnabled(),
            'users' => $users,
            'customers' => $this->getCustomers(),
            'suppliers' => $this->getSuppliers(),
            'products' => $this->getProducts(),
            'orderForms' => $this->getOrderForms(),
            'invoices' => $this->getInvoices(),
            'estimates' => $this->getEstimates(),
            'contracts' => $this->getContracts(),
            'companySettings' => $this->getCompanySettings(),
            'projects' => $this->getProjects(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }


}