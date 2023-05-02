<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\License;
use Exception;
use Service\HttpHelper;
use Service\LogManager;

class LicenseController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data): bool
    {
        // check if some data is missing, if so, return false
        if (!isset($data['name']) || !isset($data['description']) || !isset($data['price']) || !isset($data['maxUsers']) || !isset($data['validityPeriod'])) {
            return false;
        } else {
            return true;
        }
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

        // check if the data is valid
        if (!$this->validateData($requestBody)) {
            HttpHelper::sendStatusResponse(400, 'Invalid data');
            $logMessage = LogManager::getFullContext() . ' - Invalid data';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

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
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(500, $error);
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
                HttpHelper::sendStatusResponse(409, 'License already exists');
                $logMessage = LogManager::getFullContext() . ' - License already exists';
                LogManager::addErrorLog($logMessage);
                exit(1);
            }
            HttpHelper::sendStatusResponse(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendStatusResponse(201, 'License added successfully');

        // add a log
        $logMessage = LogManager::getContext() . ' - License added successfully';
        LogManager::addInfoLog($logMessage);
    }

    public function getLicenses(): void
    {
        // get all the licenses from the database
        try {
            $licenses = $this->entityManager->getRepository(License::class)->findAll();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = [];
        foreach ($licenses as $license) {
            $response[] = $license->toArray();
        }

        HttpHelper::sendDataResponse(200, $response);

        // add a log
        $logMessage = LogManager::getContext() . ' - Licenses found';
        LogManager::addInfoLog($logMessage);
    }

    public function getLicenseById(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the license is not found
        if (!$license) {
            HttpHelper::sendStatusResponse(404, 'License not found');
            $logMessage = LogManager::getFullContext() . ' - License not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $license->toArray();

        HttpHelper::sendDataResponse(200, $response);

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
        $name = $requestBody['name'] ?? false;
        $description = $requestBody['description'] ?? false;
        $price = $requestBody['price'] ?? false;
        $maxUsers = $requestBody['maxUsers'] ?? false;
        $validityPeriod = $requestBody['validityPeriod'] ?? false;

        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the license is not found
        if (!$license) {
            HttpHelper::sendStatusResponse(404, 'License not found');
            $logMessage = LogManager::getFullContext() . ' - License not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the license
        if ($name) {
            $license->setName($name);
        }

        if ($description) {
            $license->setDescription($description);
        }

        if ($price) {
            $license->setPrice($price);
        }

        if ($maxUsers) {
            $license->setMaxUsers($maxUsers);
        }

        if ($validityPeriod) {
            $license->setValidityPeriod($validityPeriod);
        }

        if (!$name && !$description && !$price && !$maxUsers && !$validityPeriod) {
            HttpHelper::sendStatusResponse(400, 'No valid data provided');
            $logMessage = LogManager::getFullContext() . ' - No valid data provided';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // persist the license
        try {
            $this->entityManager->persist($license);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendStatusResponse(200, 'License updated successfully');

        // add a log
        $logMessage = LogManager::getContext() . ' - License updated successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function deleteLicense(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the license is not found
        if (!$license) {
            HttpHelper::sendStatusResponse(404, 'License not found');
            $logMessage = LogManager::getFullContext() . ' - License not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the license
        try {
            $this->entityManager->remove($license);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendStatusResponse(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendStatusResponse(200, 'License deleted successfully');

        // add a log
        $logMessage = LogManager::getContext() . ' - License deleted successfully';
        LogManager::addInfoLog($logMessage);
    }


}