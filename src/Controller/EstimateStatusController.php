<?php

namespace Controller;

// controller for entity EstimateStatus
use Entity\EstimateStatus;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="EstimateStatusRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="EstimateStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first estimateStatus")
 * )
 *
 * @OA\Schema (
 *     schema="EstimateStatusResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="EstimateStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first estimateStatus"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class EstimateStatusController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description'];
    
    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/estimate-status",
     *     tags={"EstimateStatus"},
     *     summary="Add a new EstimateStatus",
     *     description="Add a new EstimateStatus",
     *     @OA\RequestBody(
     *         required=true,
     *         description="EstimateStatus object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="EstimateStatus created",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="EstimateStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addEstimateStatus(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//                 {
//                     "name": "EstimateStatus 1",
//                     "description": "This is the first estimateStatus"
//                 }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the EstimateStatus data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new estimateStatus
        $estimateStatus = new EstimateStatus($name, $description);

        // persist the estimateStatus
        try {
            $this->dao->add($estimateStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('EstimateStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'EstimateStatus created');

    }


    /**
     * @OA\Get(
     *     path="/estimate-status",
     *     tags={"EstimateStatus"},
     *     summary="Get all EstimateStatus",
     *     description="Get all EstimateStatus",
     *     @OA\Response(
     *         response=200,
     *         description="EstimateStatus found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EstimateStatusResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getEstimateStatuses(): void
    {
        // get all the EstimateStatus from the database
        try {
            $productFamilies = $this->dao->getAll(EstimateStatus::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $estimateStatus) {
            $response[] = $estimateStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'EstimateStatus found', $response);
    }


    /**
     * @OA\Get(
     *     path="/estimate-status/{id}",
     *     tags={"EstimateStatus"},
     *     summary="Get EstimateStatus by ID",
     *     description="Get EstimateStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the EstimateStatus to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="EstimateStatus found",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateStatusResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EstimateStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getEstimateStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $estimateStatus = $this->dao->getOneBy(EstimateStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$estimateStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus not found'));
        }

        // set the response
        $response = $estimateStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'EstimateStatus found', $response);
    }


    /**
     * @OA\Put(
     *     path="/estimate-status/{id}",
     *     tags={"EstimateStatus"},
     *     summary="Update EstimateStatus by ID",
     *     description="Update EstimateStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the EstimateStatus to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="EstimateStatus object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="EstimateStatus updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EstimateStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="EstimateStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function updateEstimateStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        //         {
        //             "name": "EstimateStatus 1",
        //             "description": "This is the first estimateStatus"
        //         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the EstimateStatus from the database by its id
        try {
            $estimateStatus = $this->dao->getOneBy(EstimateStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the estimateStatus is not found
        if (!$estimateStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus not found'));
        }

        // get the EstimateStatus data from the request body
        $name = $requestBody['name'] ?? $estimateStatus->getName();
        $description = $requestBody['description'] ?? $estimateStatus->getDescription();

        // update the estimateStatus
        $estimateStatus->setName($name);
        $estimateStatus->setDescription($description);

        // persist the estimateStatus
        try {
            $this->dao->update($estimateStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('EstimateStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'EstimateStatus updated');

    }

    /**
     * @OA\Delete(
     *     path="/estimate-status/{id}",
     *     tags={"EstimateStatus"},
     *     summary="Delete EstimateStatus by ID",
     *     description="Delete EstimateStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the EstimateStatus to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="EstimateStatus deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EstimateStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function deleteEstimateStatus(int $id): void
    {
        // get the EstimateStatus from the database by its id
        try {
            $estimateStatus = $this->dao->getOneBy(EstimateStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the EstimateStatus is not found
        if (!$estimateStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus not found'));
        }

        // remove the EstimateStatus
        try {
            $this->dao->delete($estimateStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'EstimateStatus deleted');
    }


}