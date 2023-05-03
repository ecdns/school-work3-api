<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Role;
use Entity\User;
use Exception;
use Service\Auth;
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
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $password = $requestBody['password'];
        $password = Auth::hashPassword($password); // hash the password (see Auth.php)
        $job = $requestBody['job'];
        $phone = $requestBody['phone'];
        $role = $requestBody['role'];
        $companyName = $requestBody['company'];


        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $role]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $companyName]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // if the company is not found
        if (!$company) {
            Request::handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('User already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if there are no users
        if (!$users) {
            Request::handleErrorAndQuit(404, new Exception('No users found'));
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(404, new Exception('User not found'));
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
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(404, new Exception('User not found'));
        }

        // get the company and role from the database
        try {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $user->getRole()->getName()]);
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $user->getCompany()->getName()]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role doesn't exist
        if (!$role) {
            Request::handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // if the company doesn't exist
        if (!$company) {
            Request::handleErrorAndQuit(404, new Exception('Company not found'));
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
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(404, new Exception('User not found'));
        }

        // remove the user
        try {
            $this->entityManager->remove($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            Request::handleErrorAndQuit(404, new Exception('User not found'));
        }

        // if the password is incorrect
        if (!Auth::isValidPassword($password, $user->getPassword())) {
            Request::handleErrorAndQuit(401, new Exception('Incorrect password'));
        }

        // check if the user company is active
        if (!$user->getCompany()->getIsEnabled()) {
            Request::handleErrorAndQuit(401, new Exception('Company is not active'));
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'User logged in');
    }

}