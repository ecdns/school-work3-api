<?php

namespace Model;

use Medoo\Medoo;
use Utility\AuthHelper;

class UserModel
{
    private const TABLE_NAME = 'USER';
    private Medoo $dbConnection;

    public function __construct(Medoo $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getUserById(int $id): ?array
    {
        return $this->dbConnection->select(self::TABLE_NAME, "*", ["userId" => $id]);
    }

    public function getUserByEmail(string $email): ?array
    {
        return $this->dbConnection->select(self::TABLE_NAME, "*", ["userEmail" => $email]);
    }

    public function addUser($payload): void
    {

        $userName = $payload['userName'];
        $userEmail = $payload['userEmail'];
        $userPassword = $payload['userPassword'];
        $hashedPassword = AuthHelper::hashPassword($userPassword);

        $this->dbConnection->insert(self::TABLE_NAME, [
            "userName" => $userName,
            "userEmail" => $userEmail,
            "userPassword" => $hashedPassword
        ]);
    }

    public function addJWT($jwt, $payload): void
    {

        $email = $payload['userEmail'];

        $this->dbConnection->update(self::TABLE_NAME, [
            "token" => $jwt,
        ], ["userEmail" => $email]);
    }

    public function updateUser(string $email, $payload): void
    {
        $userName = $payload['userName'];
        $userEmail = $payload['userEmail'];
        $userPassword = $payload['userPassword'];
        $hashedPassword = AuthHelper::hashPassword($userPassword);

        $this->dbConnection->update(self::TABLE_NAME, [
            "userName" => $userName,
            "userEmail" => $userEmail,
            "userPassword" => $hashedPassword
        ], ["userEmail" => $email]);
    }

    public function deleteUser(string $email): void
    {
        $this->dbConnection->delete(self::TABLE_NAME, ["userEmail" => $email]);
    }

    public function getEmailFromToken(string $jwt): array
    {
        return $this->dbConnection->select(self::TABLE_NAME, (array)"userEmail", ["token" => $jwt]);
    }
}


