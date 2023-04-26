<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Entity\License;
use Service\HttpHelper;
use Service\LogManager;

class LicenseController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addLicense(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "basic",
        //     "description": "basic license for 1 user",
        //     "price": 100,
        //     "maxUsers": 1,
        //     "validityPeriod": 1
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $price = $requestBody['price'];
        $maxUsers = $requestBody['maxUsers'];
        $validityPeriod = $requestBody['validityPeriod'];

        // create a new license
        $license = new License($name, $description, $price, $maxUsers, $validityPeriod);

        // persist the license
        try {
            $this->entityManager->persist($license);
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
        HttpHelper::setResponse(200, 'License added successfully', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - License added successfully';
        LogManager::addInfoLog($logMessage);
    }
}