<?php

namespace Controller;

// controller for entity TaskStatus
use Doctrine\ORM\EntityManager;
use Entity\TaskStatus;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="TaskStatusRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="TaskStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first taskStatus")
 * )
 *
 * @OA\Schema (
 *     schema="TaskStatusResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="TaskStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first taskStatus"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class TaskStatusController extends AbstractController
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
     *     path="/task-status",
     *     tags={"TaskStatus"},
     *     summary="Add a new TaskStatus",
     *     description="Add a new TaskStatus",
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaskStatus object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/TaskStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="TaskStatus created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="TaskStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addTaskStatus(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "TaskStatus 1",
//             "description": "This is the first taskStatus"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the TaskStatus data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new taskStatus
        $taskStatus = new TaskStatus($name, $description);

        // persist the taskStatus
        try {
            $this->dao->add($taskStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('TaskStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'TaskStatus created');

    }

    /**
     * @OA\Get(
     *     path="/task-status/all",
     *     tags={"TaskStatus"},
     *     summary="Get all TaskStatuses",
     *     description="Get all TaskStatuses",
     *     @OA\Response(
     *         response=200,
     *         description="TaskStatuses found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskStatusResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTaskStatuses(): void
    {
        // get all the TaskStatus from the database
        try {
            $productFamilies = $this->dao->getAll(TaskStatus::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $taskStatus) {
            $response[] = $taskStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskStatus found', $response);
    }

    /**
     * @OA\Get(
     *     path="/task-status/{id}",
     *     tags={"TaskStatus"},
     *     summary="Get TaskStatus by ID",
     *     description="Get TaskStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskStatus to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskStatus found",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TaskStatusResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTaskStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskStatus not found'));
        }

        // set the response
        $response = $taskStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskStatus found', $response);
    }

    /**
     * @OA\Put(
     *     path="/task-status/{id}",
     *     tags={"TaskStatus"},
     *     summary="Update TaskStatus by ID",
     *     description="Update TaskStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskStatus to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaskStatus object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/TaskStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskStatus updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="TaskStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateTaskStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "TaskStatus 1",
        //     "description": "This is the first taskStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the TaskStatus from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the taskStatus is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskStatus not found'));
        }

        // get the TaskStatus data from the request body
        $name = $requestBody['name'] ?? $taskStatus->getName();
        $description = $requestBody['description'] ?? $taskStatus->getDescription();

        // update the taskStatus
        $taskStatus->setName($name);
        $taskStatus->setDescription($description);

        // persist the taskStatus
        try {
            $this->dao->update($taskStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('TaskStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskStatus updated');

    }


    /**
     * @OA\Delete(
     *     path="/task-status/{id}",
     *     tags={"TaskStatus"},
     *     summary="Delete TaskStatus by ID",
     *     description="Delete TaskStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskStatus to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskStatus deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteTaskStatus(int $id): void
    {
        // get the TaskStatus from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the TaskStatus is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskStatus not found'));
        }

        // remove the TaskStatus
        try {
            $this->dao->delete($taskStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskStatus deleted');
    }


}