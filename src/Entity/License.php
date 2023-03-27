<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'license')]
#[ORM\HasLifecycleCallbacks]
class License implements EntityInterface
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
    private float $price;

    #[ORM\Column(type: 'integer')]
    private int $maxUsers;

    #[ORM\Column(type: 'integer')]
    private int $validityPeriod;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'license', targetEntity: Company::class)]
    private Collection $companies;

    public function __construct(string $name, string $description, float $price, int $maxUsers, int $validityPeriod)
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->maxUsers = $maxUsers;
        $this->validityPeriod = $validityPeriod;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getMaxUsers(): int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function getValidityPeriod(): int
    {
        return $this->validityPeriod;
    }

    public function setValidityPeriod(int $validityPeriod): void
    {
        $this->validityPeriod = $validityPeriod;
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
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function setCompanies(Collection $companies): void
    {
        $this->companies = $companies;
    }

    public function addCompany(Company $company): void
    {
        $this->companies->add($company);
    }

    public function removeCompany(Company $company): void
    {
        $this->companies->removeElement($company);
    }

    public function hasCompany(Company $company): bool
    {
        return $this->companies->contains($company);
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
            'price' => $this->price,
            'maxUsers' => $this->maxUsers,
            'validityPeriod' => $this->validityPeriod,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'maxUsers' => $this->maxUsers,
            'validityPeriod' => $this->validityPeriod,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'companies' => $this->companies->map(fn (Company $company) => $company->toArray())->toArray(),
        ];
    }

    public function toFullArrayWithUsers(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'maxUsers' => $this->maxUsers,
            'validityPeriod' => $this->validityPeriod,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'companies' => $this->companies->map(fn (Company $company) => $company->toFullArrayWithUsers())->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}