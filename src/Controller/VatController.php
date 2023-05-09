<?php

namespace Controller;

// controller for entity Vat
use Doctrine\ORM\EntityManager;
use Entity\Vat;
use Exception;
use Service\Request;

class VatController extends AbstractController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //function for adding a new vat
    const REQUIRED_FIELDS = ['name', 'rate', 'description'];

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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the vat data from the request body
        $name = $requestBody['name'];
        $rate = $requestBody['rate'];
        $description = $requestBody['description'];

        // create a new vat
        $vat = new Vat($name, $rate, $description);

        // persist the vat
        try {
            $this->entityManager->persist($vat);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Vat already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'Vat created');

    }

    //function for getting all vats
    public function getVats(): void
    {
        // get all the licenses from the database
        try {
            $vats = $this->entityManager->getRepository(Vat::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($vats as $vat) {
            $response[] = $vat->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Vats found', $response);
    }

    public function getVatById(int $id): void
    {
        // get the license from the database by its id
        try {
            $vat = $this->entityManager->getRepository(Vat::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$vat) {
            Request::handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // set the response
        $response = $vat->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'Vat found', $response);
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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the vat data from the request body
        $name = $requestBody['name'];
        $rate = $requestBody['rate'];
        $description = $requestBody['description'];

        // get the vat from the database by its id
        try {
            $vat = $this->entityManager->getRepository(Vat::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$vat) {
            Request::handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // update the vat
        $vat->setName($name);
        $vat->setRate($rate);
        $vat->setDescription($description);

        // persist the vat
        try {
            $this->entityManager->persist($vat);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Vat already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Vat updated');

    }

    //function for deleting a vat
    public function deleteVat(int $id): void
    {
        // get the vat from the database by its id
        try {
            $vat = $this->entityManager->getRepository(Vat::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the vat is not found
        if (!$vat) {
            Request::handleErrorAndQuit(404, new Exception('Vat not found'));
        }

        // remove the vat
        try {
            $this->entityManager->remove($vat);
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
        Request::handleSuccessAndQuit(200, 'Vat deleted');
    }


}