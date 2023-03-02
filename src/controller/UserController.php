<?php

namespace Controller;

use Exception;
use Medoo\Medoo;
use Model\UserModel;
use Utility\AuthHelper;
use Utility\HttpHelper;


class UserController
{

    private Medoo $dbConnection;
    private UserModel $userModel;
    private string $jwtKey;

    private array $headers;

    private string|false $jwt;

    public function __construct($dbConnection, $jwtKey, $headers)
    {
        $this->dbConnection = $dbConnection;
        $this->userModel = new UserModel($this->dbConnection);
        $this->jwtKey = $jwtKey;
        $this->headers = $headers;
        $this->jwt = HttpHelper::getAuthHeaderValue($this->headers);
    }

    public function addUser(): void
    {
        $payload = json_decode(file_get_contents("php://input"), true);
        if (isset($payload['userName']) && isset($payload['userEmail']) && isset($payload['userPassword'])) {
            try {
                $this->userModel->addUser($payload);
                HttpHelper::setResponse(201, "User Created", true);
            } catch (Exception $e) {
                HttpHelper::setResponse(500, "Server Error", true);
            }
        } else {
            HttpHelper::setResponse(400, "Invalid or Missing Parameters", true);
        }
    }

    public function updateUser($email): void
    {
        $this->authenticateRequest();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            HttpHelper::setResponse(400, 'Invalid Or Missing Parameters', true);
            exit();
        }

        $payload = json_decode(file_get_contents("php://input"), true);

        if (isset($payload['userName']) && isset($payload['userEmail']) && isset($payload['userPassword'])) {
            try {
                $this->userModel->updateUser($email, $payload);
                HttpHelper::setResponse(200, "User Updated", true);
            } catch (Exception $e) {
                HttpHelper::setResponse(500, "Server Error", true);
            }
        } else {
            HttpHelper::setResponse(400, "Invalid Or Missing Parameters", true);
        }

    }

    private function authenticateRequest(): void
    {
        try {
            AuthHelper::authenticateRequestToken($this->jwtKey, $this->jwt);
        } catch (Exception $e) {
            HttpHelper::setResponse(403, "Missing or Invalid Token", true);
            exit;
        }

        if (empty($this->userModel->getEmailFromToken($this->jwt))) {
            HttpHelper::setResponse(403, "Token Doesn't match any profile", true);
            exit;
        }
    }

    public function deleteUser($email): void
    {
        $this->authenticateRequest();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            HttpHelper::setResponse(400, 'Invalid Or Missing Parameters', true);
            exit();
        }

        try {
            $this->userModel->deleteUser($email);
            HttpHelper::setResponse(204, "User Deleted", true);
        } catch (Exception $e) {
            HttpHelper::setResponse(500, "Server error", true);
        }

    }

    public function createToken(): void
    {

        $payload = json_decode(file_get_contents("php://input"), true);

        if ($this->authenticateUser(true)) {

            try {
                $jwt = AuthHelper::encodeJWT($payload['userEmail'], $this->jwtKey);
            } catch (Exception $e) {
                HttpHelper::setResponse(500, 'Server Error', true);
                exit;
            }

            try {
                $this->userModel->addJWT($jwt, $payload);
                HttpHelper::setResponse(200, 'Token Created', false);
                echo json_encode(['token' => $jwt]);
            } catch (Exception $e) {
                HttpHelper::setResponse(500, "Server Error", true);
            }
        } else {
            HttpHelper::setResponse(403, "Wrong Credentials", true);
            exit;
        }
    }

    public function authenticateUser(bool $isForJwtCreation = false)
    {
        $payload = json_decode(file_get_contents("php://input"), true);

        if (isset($payload['userEmail']) && isset($payload['userPassword'])) {
            try {
                $results = $this->userModel->getUserByEmail($payload['userEmail']);
                if (empty($results)) {
                    HttpHelper::setResponse(404, 'User Not Found', true);
                    exit;
                }
                $userPassword = $payload['userPassword'];
                $hashedPassword = $results[0]['userPassword'];
                if (AuthHelper::isValidPassword($userPassword, $hashedPassword)) {
                    if (!$isForJwtCreation) HttpHelper::setResponse(200, 'Authenticated', true);
                    return true;
                } else {
                    if (!$isForJwtCreation) HttpHelper::setResponse(403, 'Wrong Credentials', true);
                    return false;
                }
            } catch (Exception $e) {
                HttpHelper::setResponse(500, "Server Error", true);
            }
        } else {
            HttpHelper::setResponse(400, "Invalid or Missing Parameters", true);
        }
    }

    public function getUserByEmail($email): void
    {
        $this->authenticateRequest();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            HttpHelper::setResponse(400, 'Invalid Email', true);
            exit();
        }

        try {
            $results = $this->userModel->getUserByEmail($email);
            if (empty($results)) {
                HttpHelper::setResponse(404, "User Not Found", true);
                exit();
            }
            HttpHelper::setResponse(200, "Success", false);
            echo json_encode($results);
        } catch (Exception $e) {
            HttpHelper::setResponse(500, "Server Error", true);
        }

    }


}