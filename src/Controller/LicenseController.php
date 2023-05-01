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
        HttpHelper::setResponse(200, 'License added successfully', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - License added successfully';
        LogManager::addInfoLog($logMessage);
    }

    public function getLicenseById(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'License not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $license->toArray();

        HttpHelper::setResponse(200, 'License found', false);
        HttpHelper::setResponseData($response);

        // add a log
        $logMessage = LogManager::getContext() . ' - License found';
        LogManager::addInfoLog($logMessage);
    }

    public function updateLicense(int $id): void
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

        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'License not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the license
        $license->setName($name);
        $license->setDescription($description);
        $license->setPrice($price);
        $license->setMaxUsers($maxUsers);
        $license->setValidityPeriod($validityPeriod);

        // persist the license
        try {
            $this->entityManager->persist($license);
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
        HttpHelper::setResponse(200, 'License updated successfully', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - License updated successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function deleteLicense(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'License not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the license
        try {
            $this->entityManager->remove($license);
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
        HttpHelper::setResponse(200, 'License deleted successfully', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - License deleted successfully';
        LogManager::addInfoLog($logMessage);
    }


}