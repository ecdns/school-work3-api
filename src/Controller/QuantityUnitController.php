<?php

namespace Controller;

use Entity\QuantityUnit;
use Exception;
use Service\DAO;
use Service\Request;

class QuantityUnitController extends AbstractController
{

    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
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
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the QuantityUnit data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $unit = $requestBody['unit'];


        // create a new quantityUnit
        $quantityUnit = new QuantityUnit($name, $unit, $description);

        // flush the entity manager
        try {
            $this->dao->addEntity($quantityUnit);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('QuantityUnit already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'QuantityUnit created');
    }

    //function for getting all quantityUnit
    public function getQuantityUnits(): void
    {
        // get all the quantityUnit from the database
        try {
            $quantityUnits = $this->dao->getAllEntities(QuantityUnit::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($quantityUnits as $quantityUnit) {
            $response[] = $quantityUnit->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'QuantityUnit found', $response);
    }

    public function getQuantityUnitById(int $id): void
    {
        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->dao->getOneEntityBy(QuantityUnit::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the quantityUnit is not found
        if (!$quantityUnit) {
            $this->request->handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // set the response
        $response = $quantityUnit->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'QuantityUnit found', $response);
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
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('QuantityUnit request data'));
        }

        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->dao->getOneEntityBy(QuantityUnit::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the quantityUnit is not found
        if (!$quantityUnit) {
            $this->request->handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // get the quantityUnit data from the request body
        $name = $requestBody['name'] ?? $quantityUnit->getName();
        $unit = $requestBody['unit'] ?? $quantityUnit->getUnit();
        $description = $requestBody['description'] ?? $quantityUnit->getDescription();

        // update the quantityUnit
        $quantityUnit->setName($name);
        $quantityUnit->setUnit($unit);
        $quantityUnit->setDescription($description);

        // flush the entity manager
        try {
            $this->dao->updateEntity($quantityUnit);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('QuantityUnit already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'QuantityUnit updated');

    }

    //function for deleting a vat
    public function deleteVat(int $id): void
    {
        // get the vat from the database by its id
        try {
            $quantityUnit = $this->dao->getOneEntityBy(QuantityUnit::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$quantityUnit) {
            $this->request->handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // remove the vat
        try {
            $this->dao->deleteEntity($quantityUnit);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'QuantityUnit deleted');
    }
}