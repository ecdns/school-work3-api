<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Role;
use Entity\User;
use Exception;
use Service\Auth;
use Service\DAO;
use Service\Request;

class UserController extends AbstractController
{
    private DAO $dao;
    private Request $request;
    private Auth $auth;
    private const REQUIRED_FIELDS = ['firstName', 'lastName', 'email', 'password', 'job', 'phone', 'role', 'company'];

    public function __construct(DAO $dao, Request $request, Auth $auth)
    {
        $this->dao = $dao;
        $this->request = $request;
        $this->auth = $auth;
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
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $password = $requestBody['password'];
        $password = $this->auth->hashPassword($password); // hash the password (see Auth.php)
        $job = $requestBody['job'];
        $phone = $requestBody['phone'];
        $role = $requestBody['role'];
        $companyName = $requestBody['company'];


        // get the company and role from the database
        try {
            $role = $this->dao->getOneEntityBy(Role::class, ['name' => $role]);
            $company = $this->dao->getOneEntityBy(Company::class, ['name' => $companyName]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // if the company is not found
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // add the user to the database
        try {
            $this->dao->addEntity($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('User already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'User created');
    }

    public function getUsers(): void
    {
        // get the users from the database
        try {
            $users = $this->dao->getAllEntities(User::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if there are no users
        if (!$users) {
            $this->request->handleErrorAndQuit(404, new Exception('No users found'));
        }

        // get the users data
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = $user->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Users found', $usersData);
    }

    public function getUserById(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->dao->getOneEntityBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // get the user data
        $userData = $user->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User found', $userData);
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
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user from the database by its id
        try {
            $user = $this->dao->getOneEntityBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'] ?? $user->getFirstName();
        $lastName = $requestBody['lastName'] ?? $user->getLastName();
        $email = $requestBody['email'] ?? $user->getEmail();
        $password = $requestBody['password'] ?? $user->getPassword();
        $job = $requestBody['job'] ?? $user->getJob();
        $phone = $requestBody['phone'] ?? $user->getPhone();
        $role = $requestBody['role'] ?? $user->getRole()->getName();
        $companyName = $requestBody['company'] ?? $user->getCompany()->getName();

        // get the company and role from the database
        try {
            $role = $this->dao->getOneEntityBy(Role::class, ['name' => $role]);
            $company = $this->dao->getOneEntityBy(Company::class, ['name' => $companyName]);

            if (!$role || !$company) {
                $this->request->handleErrorAndQuit(404, new Exception('Role or company not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // update the user data
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setJob($job);
        $user->setPhone($phone);
        $user->setRole($role);
        $user->setCompany($company);

        // update the user in the database
        try {
            $this->dao->updateEntity($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('User already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User updated');
    }

    public function deleteUser(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->dao->getOneEntityBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // remove the user
        try {
            $this->dao->deleteEntity($user);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User deleted');
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
            $user = $this->dao->getOneEntityBy(User::class, ['email' => $email]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // if the password is incorrect
        if ($this->auth->isValidPassword($password, $user->getPassword())) {
            $this->request->handleErrorAndQuit(401, new Exception('Incorrect password'));
        }

        // check if the user company is active
        if (!$user->getCompany()->getIsEnabled()) {
            $this->request->handleErrorAndQuit(401, new Exception('Company is not active'));
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User logged in');
    }

}