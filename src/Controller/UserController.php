<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Entity\Company;
use Entity\Role;
use Entity\User;
use Service\HttpHelper;
use Service\LogManager;

class UserController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $password = $requestBody['password'];
        $job = $requestBody['job'];
        $phone = $requestBody['phone'];
        $role = $requestBody['role'];
        $companyName = $requestBody['company'];

        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $role]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $companyName]);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::setResponse(201, 'User created', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - User created';
        LogManager::addInfoLog($logMessage);
    }

    public function getUserById(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user doesn't exist
        if (!$user) {
            HttpHelper::setResponse(404, 'User not found', true);
            $logMessage = LogManager::getContext() . ' - User not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the user data
        $userData = $user->toArray();

        // set the response
        HttpHelper::setResponse(200, 'User found', false);
        HttpHelper::setResponseData($userData);

        // add a log
        $logMessage = LogManager::getContext() . ' - User found';
        LogManager::addInfoLog($logMessage);
    }



}