<?php

namespace Model;

class UserModel implements ModelInterface
{

    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $password;
    private string $role;
    private string $job;
    private string $phone;
    private int $companyId;
    private string $createdAt;
    private string $updatedAt;
    private string $passwordConfirmedAt;
    private int $settingsId;
    private bool $isEnabled;


    public function __construct(int $id, string $firstName, string $lastName, string $email, string $password, string $role, string $job, string $phone, int $companyId, string $createdAt, string $updatedAt, string $passwordConfirmedAt, int $settingsId, bool $isEnabled)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->job = $job;
        $this->phone = $phone;
        $this->companyId = $companyId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->passwordConfirmedAt = $passwordConfirmedAt;
        $this->settingsId = $settingsId;
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
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

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
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

    public function getPasswordConfirmedAt(): string
    {
        return $this->passwordConfirmedAt;
    }

    public function setPasswordConfirmedAt(string $passwordConfirmedAt): void
    {
        $this->passwordConfirmedAt = $passwordConfirmedAt;
    }

    public function getSettingsId(): int
    {
        return $this->settingsId;
    }

    public function setSettingsId(int $settingsId): void
    {
        $this->settingsId = $settingsId;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRoleName(): string
    {
        return $this->role === 'admin' ? 'Administrateur' : 'Utilisateur';
    }


    public function getIsEnabledName(): string
    {
        return $this->isEnabled ? 'Activé' : 'Désactivé';
    }

    public function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['password'],
            $data['role'],
            $data['job'],
            $data['phone'],
            $data['companyId'],
            $data['createdAt'],
            $data['updatedAt'],
            $data['passwordConfirmedAt'],
            $data['settingsId'],
            $data['isEnabled'],
        );
    }

    public function toArray (): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'job' => $this->job,
            'phone' => $this->phone,
            'companyId' => $this->companyId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'passwordConfirmedAt' => $this->passwordConfirmedAt,
            'settingsId' => $this->settingsId,
            'isEnabled' => $this->isEnabled,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}






