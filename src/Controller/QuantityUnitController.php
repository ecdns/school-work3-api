<?php

namespace Controller;

// controller for entity Vat
use Doctrine\ORM\EntityManager;
use Entity\QuantityUnit;
use Entity\Vat;
use Exception;
use Service\Request;

class QuantityUnitController extends AbstractController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //function for adding a new quantityUnit
    const REQUIRED_FIELDS = ['name', 'unit', 'description'];

    public function addQuantityUnit(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Litre",
        //     "unit": "l",
        //     "description": "Litre"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the QuantityUnit data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $unit = $requestBody['unit'];


        // create a new quantityUnit
        $quantityUnit = new QuantityUnit($name, $unit, $description);

        // persist the quantityUnit
        try {
            $this->entityManager->persist($quantityUnit);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('QuantityUnit already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'QuantityUnit created');

    }

    //function for getting all quantityUnit
    public function getQuantityUnits(): void
    {
        // get all the quantityUnit from the database
        try {
            $quantityUnits = $this->entityManager->getRepository(QuantityUnit::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($quantityUnits as $quantityUnit) {
            $response[] = $quantityUnit->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'QuantityUnit found', $response);
    }

    public function getQuantityUnitById(int $id): void
    {
        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->entityManager->getRepository(QuantityUnit::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the quantityUnit is not found
        if (!$quantityUnit) {
            Request::handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // set the response
        $response = $quantityUnit->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'QuantityUnit found', $response);
    }

    //function for updating a quantityUnit
    public function updateQuantityUnit(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Litre",
        //     "unit": "l",
        //     "description": "Litre"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('QuantityUnit request data'));
        }

        // get the quantityUnit data from the request body
        $name = $requestBody['name'];
        $unit = $requestBody['unit'];
        $description = $requestBody['description'];

        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->entityManager->getRepository(QuantityUnit::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the quantityUnit is not found
        if (!$quantityUnit) {
            Request::handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // update the quantityUnit
        $quantityUnit->setName($name);
        $quantityUnit->setUnit($unit);
        $quantityUnit->setDescription($description);

        // persist the quantityUnit
        try {
            $this->entityManager->persist($quantityUnit);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('QuantityUnit already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'QuantityUnit updated');

    }

    //function for deleting a vat
    public function deleteVat(int $id): void
    {
        // get the vat from the database by its id
        try {
            $quantityUnit = $this->entityManager->getRepository(QuantityUnit::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$quantityUnit) {
            Request::handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // remove the vat
        try {
            $this->entityManager->remove($quantityUnit);
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
        Request::handleSuccessAndQuit(200, 'QuantityUnit deleted');
    }


}