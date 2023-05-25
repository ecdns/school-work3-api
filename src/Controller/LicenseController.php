<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\License;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="LicenseRequest",
 *     required={"name", "description", "price", "maxUsers", "validityPeriod"},
 *     @OA\Property(property="name", type="string", example="basic"),
 *     @OA\Property(property="description", type="string", example="basic license for 1 user"),
 *     @OA\Property(property="price", type="integer", example=100),
 *     @OA\Property(property="maxUsers", type="integer", example=1),
 *     @OA\Property(property="validityPeriod", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="LicenseResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="basic"),
 *     @OA\Property(property="description", type="string", example="basic license for 1 user"),
 *     @OA\Property(property="price", type="integer", example=100),
 *     @OA\Property(property="maxUsers", type="integer", example=1),
 *     @OA\Property(property="validityPeriod", type="integer", example=1),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-09-30 12:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-09-30 12:00:00")
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/license",
     *     tags={"License"},
     *     summary="Add a new license",
     *     description="Add a new license to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="License data",
     *         @OA\JsonContent(ref="#/components/schemas/LicenseRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="License created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="License already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
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



    /**
     * @OA\Get(
     *     path="/license/all",
     *     tags={"License"},
     *     summary="Get all licenses",
     *     description="Get all licenses from the database",
     *     @OA\Response(
     *         response="200",
     *         description="Licenses found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LicenseResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/license/{id}",
     *     tags={"License"},
     *     summary="Get a license by ID",
     *     description="Get a license from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the license to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="License found",
     *         @OA\JsonContent(ref="#/components/schemas/LicenseResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="License not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/license/{id}",
     *     tags={"License"},
     *     summary="Update a license by ID",
     *     description="Update a license from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the license to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="License object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/LicenseRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="License updated"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="License not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="License already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
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


    /**
     * @OA\Delete(
     *     path="/license/{id}",
     *     tags={"License"},
     *     summary="Delete a license by ID",
     *     description="Delete a license from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the license to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="License deleted"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="License not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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