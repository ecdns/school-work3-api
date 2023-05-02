<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Role;
use Exception;
use Service\HttpHelper;
use Service\LogManager;

class RoleController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addRole(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Admin",
        //     "description": "Administrator"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new role
        $role = new Role($name, $description);

        // persist the role
        try {
            $this->entityManager->persist($role);
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
                HttpHelper::sendRequestState(409, 'License already exists');
                $logMessage = LogManager::getFullContext() . ' - License already exists';
                LogManager::addErrorLog($logMessage);
                exit(1);
            }
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(201, 'Role created');

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role created';
        LogManager::addInfoLog($logMessage);
    }

    public function getRoles(): void
    {
        // get all roles
        try {
            $roles = $this->entityManager->getRepository(Role::class)->findAll();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = [];
        foreach ($roles as $role) {
            $response[] = $role->toArray();
        }

        HttpHelper::sendRequestData(200, $response);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Roles found';
        LogManager::addInfoLog($logMessage);
    }

    public function getRoleById(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
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
            $logMessage = LogManager::getFullContext() . ' - ' . 'Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $role->toArray();

        HttpHelper::sendRequestData(200, $response);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role found';
        LogManager::addInfoLog($logMessage);
    }

    public function updateRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
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
            $logMessage = LogManager::getFullContext() . ' - ' . 'Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Admin",
        //     "description": "Administrator"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $name = $requestBody['name'] ?? false;
        $description = $requestBody['description'] ?? false;

        // update the role
        if ($name) {
            $role->setName($name);
        }

        if ($description) {
            $role->setDescription($description);
        }

        // if no data was provided
        if (!$name && !$description) {
            HttpHelper::sendRequestState(400, 'No valid data provided');
            $logMessage = LogManager::getContext() . ' - ' . 'No valid data provided';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // persist the role
        try {
            $this->entityManager->persist($role);
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
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'Role updated');

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role updated';
        LogManager::addInfoLog($logMessage);

    }

    public function deleteRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
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
            $logMessage = LogManager::getContext() . ' - ' . 'Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // remove the role
        try {
            $this->entityManager->remove($role);
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
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'Role deleted');

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role deleted';
        LogManager::addInfoLog($logMessage);
    }
}