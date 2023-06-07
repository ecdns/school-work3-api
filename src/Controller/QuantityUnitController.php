<?php

namespace Controller;

use Entity\QuantityUnit;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="QuantityUnitRequest",
 *     required={"name", "unit", "description"},
 *     @OA\Property(property="name", type="string", example="Litre"),
 *     @OA\Property(property="unit", type="string", example="l"),
 *     @OA\Property(property="description", type="string", example="Litre")
 * )
 *
 * @OA\Schema (
 *     schema="QuantityUnitResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Litre"),
 *     @OA\Property(property="unit", type="string", example="l"),
 *     @OA\Property(property="description", type="string", example="Litre"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-03-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-03-01 00:00:00")
 * )
 *
 */
class QuantityUnitController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'unit', 'description'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/quantityUnit",
     *     tags={"QuantityUnit"},
     *     summary="Add a new QuantityUnit",
     *     description="Add a new QuantityUnit",
     *     @OA\RequestBody(
     *         required=true,
     *         description="QuantityUnit data",
     *         @OA\JsonContent(ref="#/components/schemas/QuantityUnitRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="QuantityUnit created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="QuantityUnit already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addQuantityUnit(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Litre",
//             "unit": "l",
//             "description": "Litre"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
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
            $this->dao->add($quantityUnit);
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

    /**
     * @OA\Get(
     *     path="/quantityUnit/all",
     *     tags={"QuantityUnit"},
     *     summary="Get all QuantityUnits",
     *     description="Get all QuantityUnits",
     *     @OA\Response(
     *         response=200,
     *         description="QuantityUnits found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/QuantityUnitResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getQuantityUnits(): void
    {
        // get all the quantityUnit from the database
        try {
            $quantityUnits = $this->dao->getAll(QuantityUnit::class);
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

    /**
     * @OA\Get(
     *     path="/quantityUnit/{id}",
     *     tags={"QuantityUnit"},
     *     summary="Get a QuantityUnit by ID",
     *     description="Get a QuantityUnit by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the QuantityUnit to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QuantityUnit found",
     *         @OA\JsonContent(ref="#/components/schemas/QuantityUnitResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QuantityUnit not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getQuantityUnitById(int $id): void
    {
        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->dao->getOneBy(QuantityUnit::class, ['id' => $id]);
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


    /**
     * @OA\Put(
     *     path="/quantityUnit/{id}",
     *     tags={"QuantityUnit"},
     *     summary="Update a QuantityUnit by ID",
     *     description="Update a QuantityUnit by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the QuantityUnit to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="QuantityUnit object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/QuantityUnitRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QuantityUnit updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QuantityUnit not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="QuantityUnit already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('QuantityUnit request data'));
        }

        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->dao->getOneBy(QuantityUnit::class, ['id' => $id]);
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
            $this->dao->update($quantityUnit);
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


    /**
     * @OA\Delete(
     *     path="/quantityUnit/{id}",
     *     tags={"QuantityUnit"},
     *     summary="Delete a QuantityUnit by ID",
     *     description="Delete a QuantityUnit by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the QuantityUnit to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QuantityUnit deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QuantityUnit not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteQuantityUnit(int $id): void
    {
        // get the quantityUnit from the database by its id
        try {
            $quantityUnit = $this->dao->getOneBy(QuantityUnit::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the quantityUnit is not found
        if (!$quantityUnit) {
            $this->request->handleErrorAndQuit(404, new Exception('QuantityUnit not found'));
        }

        // remove the quantityUnit
        try {
            $this->dao->delete($quantityUnit);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'QuantityUnit deleted');
    }

}