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
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // set the response
        HttpHelper::setResponse(201, 'Role created', true);

        // log the response
        $logMessage = LogManager::getContext() . ' - ' . 'Role created';
        LogManager::addInfoLog($logMessage);
    }
}