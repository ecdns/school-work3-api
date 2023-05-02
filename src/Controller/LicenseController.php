<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\License;
use Exception;
use Service\RequestManager;

class LicenseController implements ControllerInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data, bool $isPostRequest = true): bool
    {
        if ($isPostRequest) {
            if (!isset($data['name']) || !isset($data['description']) || !isset($data['price']) || !isset($data['maxUsers']) || !isset($data['validityPeriod'])) {
                return false;
            } else {
                return true;
            }
        } else {
            if (isset($data['name']) || isset($data['description']) || isset($data['price']) || isset($data['maxUsers']) || isset($data['validityPeriod'])) {
                return true;
            } else {
                return false;
            }
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
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
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
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                RequestManager::handleErrorAndQuit(new Exception('License already exists'), 409);
            }
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(201, 'License created');
    }

    public function getLicenses(): void
    {
        // get all the licenses from the database
        try {
            $licenses = $this->entityManager->getRepository(License::class)->findAll();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // set the response
        $response = [];
        foreach ($licenses as $license) {
            $response[] = $license->toArray();
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'Licenses found', $response);
    }

    public function getLicenseById(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the license is not found
        if (!$license) {
            RequestManager::handleErrorAndQuit(new Exception('License not found'), 404);
        }

        // set the response
        $response = $license->toArray();

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'License found', $response);
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

        // check if the data is valid
        if (!$this->validateData($requestBody, false)) {
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

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
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the license is not found
        if (!$license) {
            RequestManager::handleErrorAndQuit(new Exception('License not found'), 404);
        }

        // update the license
        $license->setName($name ?? $license->getName());
        $license->setDescription($description ?? $license->getDescription());
        $license->setPrice($price ?? $license->getPrice());
        $license->setMaxUsers($maxUsers ?? $license->getMaxUsers());
        $license->setValidityPeriod($validityPeriod ?? $license->getValidityPeriod());

        // persist the license
        try {
            $this->entityManager->persist($license);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                RequestManager::handleErrorAndQuit(new Exception('License already exists'), 409);
            }
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // set the response
        RequestManager::handleSuccessAndQuit(200, 'License updated');

    }

    public function deleteLicense(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->entityManager->getRepository(License::class)->find($id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the license is not found
        if (!$license) {
            RequestManager::handleErrorAndQuit(new Exception('License not found'), 404);
        }

        // remove the license
        try {
            $this->entityManager->remove($license);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'License deleted');
    }


}