<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Entity\Role;
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
        HttpHelper::setResponse(201, 'Role created', true);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role created';
        LogManager::addInfoLog($logMessage);
    }

    public function getRoleById(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the role is not found
        if (!$role) {
            HttpHelper::setResponse(404, 'Role not found', true);
            $logMessage = LogManager::getContext() . ' - ' . 'Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $role->toArray();

        HttpHelper::setResponse(200, 'Role found', false);
        HttpHelper::setResponseData($response);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role found';
        LogManager::addInfoLog($logMessage);
    }

    public function updateRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the role is not found
        if (!$role) {
            HttpHelper::setResponse(404, 'Role not found', true);
            $logMessage = LogManager::getContext() . ' - ' . 'Role not found';
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
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // update the role
        $role->setName($name);
        $role->setDescription($description);

        // persist the role
        try {
            $this->entityManager->persist($role);
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
        HttpHelper::setResponse(200, 'Role updated', true);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role updated';
        LogManager::addInfoLog($logMessage);

    }

    public function deleteRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the role is not found
        if (!$role) {
            HttpHelper::setResponse(404, 'Role not found', true);
            $logMessage = LogManager::getContext() . ' - ' . 'Role not found';
            LogManager::addInfoLog($logMessage);
            exit(1);
        }

        // remove the role
        try {
            $this->entityManager->remove($role);
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
        HttpHelper::setResponse(200, 'Role deleted', true);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role deleted';
        LogManager::addInfoLog($logMessage);
    }
}