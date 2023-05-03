<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Role;
use Entity\User;
use Exception;
use Service\Request;

class UserController extends AbstractController
{
    private EntityManager $entityManager;
    private const REQUIRED_FIELDS = ['firstName', 'lastName', 'email', 'password', 'job', 'phone', 'role', 'company'];

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

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(new Exception('Role not found'), 404);
        }

        // if the company is not found
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(new Exception('User already exists'), 409);
            }
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'User created');
    }

    public function getUsers(): void
    {
        // get the users from the database
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if there are no users
        if (!$users) {
            Request::handleErrorAndQuit(new Exception('No users found'), 404);
        }

        // get the users data
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = $user->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Users found', $usersData);
    }

    public function getUserById(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // get the user data
        $userData = $user->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'User found', $userData);
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

        // validate the data
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }


        // get the user data from the request body
        $firstName = $requestBody['firstName'] ?? null;
        $lastName = $requestBody['lastName'] ?? null;
        $email = $requestBody['email'] ?? null;
        $password = $requestBody['password'] ?? null;
        $job = $requestBody['job'] ?? null;
        $phone = $requestBody['phone'] ?? null;
        $role = $requestBody['role'] ?? null;
        $companyName = $requestBody['company'] ?? null;

        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $user->getRole()->getName()]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $user->getCompany()->getName()]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the role doesn't exist
        if (!$role) {
            Request::handleErrorAndQuit(new Exception('Role not found'), 404);
        }

        // if the company doesn't exist
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // update the user data
        $user->setFirstName($firstName ?? $user->getFirstName());
        $user->setLastName($lastName ?? $user->getLastName());
        $user->setEmail($email ?? $user->getEmail());
        $user->setPassword($password ?? $user->getPassword());
        $user->setJob($job ?? $user->getJob());
        $user->setPhone($phone ?? $user->getPhone());
        $user->setRole($role);
        $user->setCompany($company);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'User updated');
    }

    public function deleteUser(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // remove the user
        try {
            $this->entityManager->remove($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'User deleted');
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // if the password is incorrect
        if (!password_verify($password, $user->getPassword())) {
            Request::handleErrorAndQuit(new Exception('Incorrect password'), 401);
        }

        // check if the user company is active
        if (!$user->getCompany()->getIsEnabled()) {
            Request::handleErrorAndQuit(new Exception('Company is not active'), 401);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'User logged in');
    }

}