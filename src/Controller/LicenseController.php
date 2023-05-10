<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\License;
use Exception;
use Service\DAO;
use Service\Request;

class LicenseController extends AbstractController
{
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'price', 'maxUsers', 'validityPeriod'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
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
            $this->dao->addEntity($license);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('License already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'License created');
    }

    public function getLicenses(): void
    {
        // get all the licenses from the database
        try {
            $licenses = $this->dao->getAllEntities(License::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($licenses as $license) {
            $response[] = $license->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Licenses found', $response);
    }

    public function getLicenseById(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->dao->getOneEntityBy(License::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$license) {
            $this->request->handleErrorAndQuit(404, new Exception('License not found'));
        }

        // set the response
        $response = $license->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'License found', $response);
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
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the license from the database by its id
        try {
            $license = $this->dao->getOneEntityBy(License::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$license) {
            $this->request->handleErrorAndQuit(404, new Exception('License not found'));
        }

        // get the user data from the request body
        $name = $requestBody['name'] ?? $license->getName();
        $description = $requestBody['description'] ?? $license->getDescription();
        $price = $requestBody['price'] ?? $license->getPrice();
        $maxUsers = $requestBody['maxUsers'] ?? $license->getMaxUsers();
        $validityPeriod = $requestBody['validityPeriod'] ?? $license->getValidityPeriod();

        // update the license
        $license->setName($name);
        $license->setDescription($description);
        $license->setPrice($price);
        $license->setMaxUsers($maxUsers);
        $license->setValidityPeriod($validityPeriod);

        // persist the license
        try {
            $this->dao->updateEntity($license);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('License already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'License updated');

    }

    public function deleteLicense(int $id): void
    {
        // get the license from the database by its id
        try {
            $license = $this->dao->getOneEntityBy(License::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$license) {
            $this->request->handleErrorAndQuit(404, new Exception('License not found'));
        }

        // remove the license
        try {
            $this->dao->deleteEntity($license);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'License deleted');
    }
}