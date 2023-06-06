<?php

namespace Controller;

// controller for entity CustomerStatus
use Entity\CustomerStatus;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="CustomerStatusRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="CustomerStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first customerStatus")
 * )
 * @OA\Schema (
 *     schema="CustomerStatusResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="CustomerStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first customerStatus"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class CustomerStatusController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'description'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/customer-status",
     *     tags={"CustomerStatus"},
     *     summary="Add a new CustomerStatus",
     *     description="Add a new CustomerStatus",
     *     @OA\RequestBody(
     *         required=true,
     *         description="CustomerStatus object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="CustomerStatus created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="CustomerStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addCustomerStatus(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        /*         {
                     "name": "CustomerStatus 1",
                     "description": "This is the first customerStatus"
                 }*/

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the CustomerStatus data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new customerStatus
        $customerStatus = new CustomerStatus($name, $description);

        // persist the customerStatus
        try {
            $this->dao->add($customerStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('CustomerStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'CustomerStatus created');

    }


    /**
     * @OA\Get(
     *     path="/customer-status/all",
     *     tags={"CustomerStatus"},
     *     summary="Get all CustomerStatuses",
     *     description="Get all CustomerStatuses",
     *     @OA\Response(
     *         response=200,
     *         description="CustomerStatuses found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerStatusResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getCustomerStatuses(): void
    {
        // get all the CustomerStatus from the database
        try {
            $productFamilies = $this->dao->getAll(CustomerStatus::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $customerStatus) {
            $response[] = $customerStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'CustomerStatus found', $response);
    }


    /**
     * @OA\Get(
     *     path="/customer-status/{id}",
     *     tags={"CustomerStatus"},
     *     summary="Get a CustomerStatus by ID",
     *     description="Get a CustomerStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CustomerStatus to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CustomerStatus found",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerStatusResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CustomerStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getCustomerStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $customerStatus = $this->dao->getOneBy(CustomerStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$customerStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('CustomerStatus not found'));
        }

        // set the response
        $response = $customerStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'CustomerStatus found', $response);
    }


    /**
     * @OA\Put(
     *     path="/customer-status/{id}",
     *     tags={"CustomerStatus"},
     *     summary="Update a CustomerStatus by ID",
     *     description="Update a CustomerStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CustomerStatus to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="CustomerStatus object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CustomerStatus updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CustomerStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="CustomerStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function updateCustomerStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "CustomerStatus 1",
        //     "description": "This is the first customerStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the CustomerStatus from the database by its id
        try {
            $customerStatus = $this->dao->getOneBy(CustomerStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customerStatus is not found
        if (!$customerStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('CustomerStatus not found'));
        }

        // get the CustomerStatus data from the request body
        $name = $requestBody['name'] ?? $customerStatus->getName();
        $description = $requestBody['description'] ?? $customerStatus->getDescription();

        // update the customerStatus
        $customerStatus->setName($name);
        $customerStatus->setDescription($description);

        // persist the customerStatus
        try {
            $this->dao->update($customerStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('CustomerStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'CustomerStatus updated');

    }


    /**
     * @OA\Delete(
     *     path="/customer-status/{id}",
     *     tags={"CustomerStatus"},
     *     summary="Delete a CustomerStatus by ID",
     *     description="Delete a CustomerStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CustomerStatus to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CustomerStatus deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CustomerStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function deleteCustomerStatus(int $id): void
    {
        // get the CustomerStatus from the database by its id
        try {
            $customerStatus = $this->dao->getOneBy(CustomerStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the CustomerStatus is not found
        if (!$customerStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('CustomerStatus not found'));
        }

        // remove the CustomerStatus
        try {
            $this->dao->delete($customerStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'CustomerStatus deleted');
    }

}