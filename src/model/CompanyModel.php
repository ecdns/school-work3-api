<?php

namespace Model;

class CompanyModel implements ModelInterface
{

    private int $id;
    private string $name;
    private string $address;
    private string $city;
    private string $country;
    private string $zipCode;
    private string $phone;
    private string $slogan;
    private string $logoPath;
    private int $licenseId;
    private string $licenseExpirationDate;
    private string $language;
    private string $createdAt;
    private string $updatedAt;
    private bool $isEnabled;

    public function __construct(int $id, string $name, string $address, string $city, string $country, string $zipCode, string $phone, string $slogan, string $logoPath, int $licenseId, string $licenseExpirationDate, string $language, string $createdAt, string $updatedAt, bool $isEnabled)
    {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->phone = $phone;
        $this->slogan = $slogan;
        $this->logoPath = $logoPath;
        $this->licenseId = $licenseId;
        $this->licenseExpirationDate = $licenseExpirationDate;
        $this->language = $language;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isEnabled = $isEnabled;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
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

    public function getLicenseId(): int
    {
        return $this->licenseId;
    }

    public function setLicenseId(int $licenseId): void
    {
        $this->licenseId = $licenseId;
    }

    public function getLicenseExpirationDate(): string
    {
        return $this->licenseExpirationDate;
    }

    public function setLicenseExpirationDate(string $licenseExpirationDate): void
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

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['address'],
            $data['city'],
            $data['country'],
            $data['zipCode'],
            $data['phone'],
            $data['slogan'],
            $data['logoPath'],
            $data['licenseId'],
            $data['licenseExpirationDate'],
            $data['language'],
            $data['createdAt'],
            $data['updatedAt'],
            $data['isEnabled']
        );
    }

    public function toArray (): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'slogan' => $this->slogan,
            'logoPath' => $this->logoPath,
            'licenseId' => $this->licenseId,
            'licenseExpirationDate' => $this->licenseExpirationDate,
            'language' => $this->language,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'isEnabled' => $this->isEnabled
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}