<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Role;
use Entity\User;
use Exception;
use Service\HttpHelper;
use Service\LogManager;

class UserController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data)
    {
        // check if one is missing and if so, return false
        if (
            !isset($data['firstName']) ||
            !isset($data['lastName']) ||
            !isset($data['email']) ||
            !isset($data['password']) ||
            !isset($data['job']) ||
            !isset($data['phone']) ||
            !isset($data['role']) ||
            !isset($data['company'])
        ) {
            return false;
        } else {
            return true;
        }
    }



    public function addUser(): void
    {

        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "firstName": "John",
        //     "lastName": "Doe",
        //     "email": "john.doe@gmail.com",
        //     "password": "password",
        //     "job": "Developer",
        //     "phone": "0123456789",
        //     "role": "Admin",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        $dataIsValid = $this->validateData($requestBody);

        // if the data is not valid
        if (!$dataIsValid) {
            HttpHelper::sendRequestState(400, 'Invalid data');
            $logMessage = LogManager::getFullContext() . ' - Invalid data';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $password = $requestBody['password'];
        $password = password_hash($password, PASSWORD_DEFAULT);
        $job = $requestBody['job'];
        $phone = $requestBody['phone'];
        $role = $requestBody['role'];
        $companyName = $requestBody['company'];


        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $role]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $companyName]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the role is not found
        if (!$role) {
            HttpHelper::sendRequestState(404, 'Role not found');
            $logMessage = LogManager::getFullContext() . ' - Role not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                HttpHelper::sendRequestState(409, 'User already exists');
                $logMessage = LogManager::getFullContext() . ' - User already exists';
                LogManager::addErrorLog($logMessage);
                exit(1);
            }
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(201, 'User created');

        // add a log
        $logMessage = LogManager::getContext() . ' - User created';
        LogManager::addInfoLog($logMessage);
    }

    public function getUsers(): void
    {
        // get the users from the database
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if there are no users
        if (!$users) {
            HttpHelper::sendRequestState(404, 'Users not found');
            $logMessage = LogManager::getFullContext() . ' - Users not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the users data
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = $user->toArray();
        }

        // set the response
        HttpHelper::sendRequestData(200, $usersData);

        // add a log
        $logMessage = LogManager::getContext() . ' - Users found';
        LogManager::addInfoLog($logMessage);
    }

    public function getUserById(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the user data
        $userData = $user->toArray();

        // set the response

        HttpHelper::sendRequestData(200, $userData);

        // add a log
        $logMessage = LogManager::getContext() . ' - User found';
        LogManager::addInfoLog($logMessage);
    }

    public function getUserByEmail(string $email): void
    {
        // get the user from the database by its email
        try {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the user data
        $userData = $user->toArray();

        // set the response
        HttpHelper::sendRequestData(200, $userData);

        // add a log
        $logMessage = LogManager::getContext() . ' - User found';
        LogManager::addInfoLog($logMessage);
    }

    public function updateUser(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "firstName": "John",
        //     "lastName": "Doe",
        //     "email": "john.doe@gmail",
        //     "password": "password",
        //     "job": "Developer",
        //     "phone": "0123456789",
        //     "role": "Admin",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $firstName = $requestBody['firstName'] ?? false;
        $lastName = $requestBody['lastName'] ?? false;
        $email = $requestBody['email'] ?? false;
        $password = $requestBody['password'] ?? false;
        $job = $requestBody['job'] ?? false;
        $phone = $requestBody['phone'] ?? false;
        $role = $requestBody['role'] ?? false;
        $companyName = $requestBody['company'] ?? false;

        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $user->getRole()->getName()]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $user->getCompany()->getName()]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the role doesn't exist
        if (!$role) {
            HttpHelper::sendRequestState(404, 'Role not found');
            $logMessage = LogManager::getContext() . ' - Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // if the company doesn't exist
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getContext() . ' - Company not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // if firstName is not null, set the user's firstName
        if ($firstName) {
            $user->setFirstName($firstName);
        }

        // if lastName is not null, set the user's lastName
        if ($lastName) {
            $user->setLastName($lastName);
        }

        // if email is not null, set the user's email
        if ($email) {
            $user->setEmail($email);
        }

        // if password is not null, set the user's password
        if ($password) {
            $user->setPassword($password);
        }

        // if job is not null, set the user's job
        if ($job) {
            $user->setJob($job);
        }

        // if phone is not null, set the user's phone
        if ($phone) {
            $user->setPhone($phone);
        }

        // if role is not null, set the user's role
        if ($role) {
            $user->setRole($role);
        }

        // if company is not null, set the user's company
        if ($company) {
            $user->setCompany($company);
        }

        // if no data was provided
        if (!$firstName && !$lastName && !$email && !$password && !$job && !$phone && !$role && !$company) {
            HttpHelper::sendRequestState(400, 'No valid data provided');
            $logMessage = LogManager::getContext() . ' - No valid data provided';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'User updated');

        // add a log
        $logMessage = LogManager::getContext() . ' - User updated';
        LogManager::addInfoLog($logMessage);
    }

    public function deleteUser(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // remove the user
        try {
            $this->entityManager->remove($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'User deleted');

        // add a log
        $logMessage = LogManager::getContext() . ' - User deleted';
        LogManager::addInfoLog($logMessage);
    }

    public function loginUser(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "email": "john.doe@gmail",
        //     "password": "password"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $email = $requestBody['email'];
        $password = $requestBody['password'];

        // get the user from the database by its email
        try {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // if the password is incorrect
        if (!password_verify($password, $user->getPassword())) {
            HttpHelper::sendRequestState(401, 'Incorrect password');
            $logMessage = LogManager::getContext() . ' - Incorrect password';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // check if the user company is active
        if (!$user->getCompany()->getIsEnabled()) {
            HttpHelper::sendRequestState(401, 'Company not active');
            $logMessage = LogManager::getContext() . ' - Company not active';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'User logged in');

        // add a log
        $logMessage = LogManager::getContext() . ' - User logged in';
        LogManager::addInfoLog($logMessage);
    }

}