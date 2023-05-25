<?php

namespace Controller;

use Entity\Vat;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="VatRequest",
 *     required={"name", "rate", "description"},
 *     @OA\Property(property="name", type="string", example="20%"),
 *     @OA\Property(property="rate", type="integer", example=20),
 *     @OA\Property(property="description", type="string", example="20% VAT")
 * )
 *
 * @OA\Schema (
 *     schema="VatResponse",
 *     required={"id", "name", "rate", "description"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="20%"),
 *     @OA\Property(property="rate", type="integer", example=20),
 *     @OA\Property(property="description", type="string", example="20% VAT"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/vat",
     *     tags={"Vat"},
     *     summary="Add a new vat",
     *     description="Add a new vat",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Vat object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/VatRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Vat created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Vat already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addVat(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

//         it will look like this:
//         {
//             "name": "20%",
//             "rate": 20,
//             "description": "20% VAT"
//         }

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

    /**
     * @OA\Get(
     *     path="/vat/all",
     *     tags={"Vat"},
     *     summary="Get all vats",
     *     description="Get all vats",
     *     @OA\Response(
     *         response=200,
     *         description="Vats found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/VatResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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



    /**
     * @OA\Get(
     *     path="/vat/{id}",
     *     tags={"Vat"},
     *     summary="Get a vat by id",
     *     description="Get a vat by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vat to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vat found",
     *         @OA\JsonContent(ref="#/components/schemas/VatResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vat not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/vat/{id}",
     *     tags={"Vat"},
     *     summary="Update a vat by id",
     *     description="Update a vat by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vat to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Vat object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/VatRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vat updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vat not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Vat already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/vat/{id}",
     *     tags={"Vat"},
     *     summary="Delete a vat by id",
     *     description="Delete a vat by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vat to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vat deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vat not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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