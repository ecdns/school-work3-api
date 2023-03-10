<?php

namespace Model;

class CustomerModel implements ModelInterface
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $address;
    private string $city;
    private string $country;
    private string $zipCode;
    private string $phone;
    private string $createdAt;
    private string $updatedAt;
    private string $customerCompanyName;
    private int $companyId;
    private int $userId;
    private int $statusId;
    private int $customerNoteId;

    public function __construct(int $id, string $firstName, string $lastName, string $email, string $address, string $city, string $country, string $zipCode, string $phone, string $createdAt, string $updatedAt, string $customerCompanyName, int $companyId, int $userId, int $statusId, int $customerNoteId)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->phone = $phone;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->customerCompanyName = $customerCompanyName;
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->statusId = $statusId;
        $this->customerNoteId = $customerNoteId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getCustomerCompanyName(): string
    {
        return $this->customerCompanyName;
    }

    public function setCustomerCompanyName(string $customerCompanyName): void
    {
        $this->customerCompanyName = $customerCompanyName;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): void
    {
        $this->statusId = $statusId;
    }

    public function getCustomerNoteId(): int
    {
        return $this->customerNoteId;
    }

    public function setCustomerNoteId(int $customerNoteId): void
    {
        $this->customerNoteId = $customerNoteId;
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['address'],
            $data['city'],
            $data['country'],
            $data['zipCode'],
            $data['phone'],
            $data['createdAt'],
            $data['updatedAt'],
            $data['customerCompanyName'],
            $data['companyId'],
            $data['userId'],
            $data['statusId'],
            $data['customerNoteId'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'zipCode' => $this->zipCode,
            'phone' => $this->phone,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'customerCompanyName' => $this->customerCompanyName,
            'companyId' => $this->companyId,
            'userId' => $this->userId,
            'statusId' => $this->statusId,
            'customerNoteId' => $this->customerNoteId,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}