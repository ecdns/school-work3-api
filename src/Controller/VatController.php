<?php

namespace Controller;

// controller for entity Vat
use Doctrine\ORM\EntityManager;
use Entity\Vat;
use Exception;
use Service\DAO;
use Service\Request;

class VatController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'rate', 'description'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    public function addVat(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "20%",
        //     "rate": 20,
        //     "description": "20% VAT"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the vat data from the request body
        $name = $requestBody['name'];
        $rate = $requestBody['rate'];
        $description = $requestBody['description'];

        // create a new vat
        $vat = new Vat($name, $rate, $description);

        // flush the entity manager
        try {
            $this->dao->addEntity($vat);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Vat already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Vat created');

    }

    //function for getting all vats
    public function getVats(): void
    {
        // get all the licenses from the database
        try {
            $vats = $this->dao->getAllEntities(Vat::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($vats as $vat) {
            $response[] = $vat->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Vats found', $response);
    }

    public function getVatById(int $id): void
    {
        // get the license from the database by its id
        try {
            $vat = $this->dao->getOneEntityBy(Vat::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$vat) {
            $this->request->handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // set the response
        $response = $vat->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Vat found', $response);
    }

    //function for updating a vat
    public function updateVat(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "20%",
        //     "rate": 20,
        //     "description": "20% VAT"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the vat from the database by its id
        try {
            $vat = $this->dao->getOneEntityBy(Vat::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$vat) {
            $this->request->handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // get the vat data from the request body
        $name = $requestBody['name'] ?? $vat->getName();
        $rate = $requestBody['rate'] ?? $vat->getRate();
        $description = $requestBody['description'] ?? $vat->getDescription();

        // update the vat
        $vat->setName($name);
        $vat->setRate($rate);
        $vat->setDescription($description);

        // flush the entity manager
        try {
            $this->dao->updateEntity($vat);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Vat already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Vat updated');

    }

    //function for deleting a vat
    public function deleteVat(int $id): void
    {
        // get the vat from the database by its id
        try {
            $vat = $this->dao->getOneEntityBy(Vat::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$vat) {
            $this->request->handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // remove the vat
        try {
            $this->dao->deleteEntity($vat);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Vat deleted');
    }
}