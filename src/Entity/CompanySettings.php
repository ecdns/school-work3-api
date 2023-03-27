<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'company_settings')]
#[ORM\HasLifecycleCallbacks]
class CompanySettings implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $primaryColor;

    #[ORM\Column(type: 'string')]
    private string $secondaryColor;

    #[ORM\Column(type: 'string')]
    private string $tertiaryColor;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime|null $updated_at = null;

    #[ORM\OneToOne(targetEntity: Company::class, cascade: ['persist', 'remove'])]
    private Company $company;

    public function __construct(string $primaryColor, string $secondaryColor, string $tertiaryColor, Company $company)
    {
        $this->primaryColor = $primaryColor;
        $this->secondaryColor = $secondaryColor;
        $this->tertiaryColor = $tertiaryColor;
        $this->company = $company;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function getSecondaryColor(): string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(string $secondaryColor): void
    {
        $this->secondaryColor = $secondaryColor;
    }

    public function getTertiaryColor(): string
    {
        return $this->tertiaryColor;
    }

    public function setTertiaryColor(string $tertiaryColor): void
    {
        $this->tertiaryColor = $tertiaryColor;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updated_at;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'primaryColor' => $this->primaryColor,
            'secondaryColor' => $this->secondaryColor,
            'tertiaryColor' => $this->tertiaryColor,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'company' => $this->company->toArray()
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }


}