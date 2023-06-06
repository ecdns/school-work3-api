<?php

namespace Controller;

// controller for entity TaskStatus
use Entity\TaskStatus;
use Entity\TaskType;
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
class TaskTypeController extends AbstractController
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
     *     tags={"TaskType"},
     *     summary="Add a new TaskType",
     *     description="Add a new TaskType",
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaskType object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/TaskTypeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="TaskType created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="TaskType already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addTaskType(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "TaskType 1",
//             "description": "This is the first taskStatus"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the TaskType data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new taskStatus
        $taskStatus = new TaskType($name, $description);

        // persist the taskStatus
        try {
            $this->dao->add($taskStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('TaskType already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'TaskType created');

    }

    /**
     * @OA\Get(
     *     path="/task-status/all",
     *     tags={"TaskType"},
     *     summary="Get all TaskTypees",
     *     description="Get all TaskTypees",
     *     @OA\Response(
     *         response=200,
     *         description="TaskTypees found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskTypeResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTaskTypes(): void
    {
        // get all the TaskType from the database
        try {
            $productFamilies = $this->dao->getAll(TaskType::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $taskStatus) {
            $response[] = $taskStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskType found', $response);
    }

    /**
     * @OA\Get(
     *     path="/task-status/{id}",
     *     tags={"TaskType"},
     *     summary="Get TaskType by ID",
     *     description="Get TaskType by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskType to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskType found",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TaskTypeResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskType not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTaskTypeById(int $id): void
    {
        // get the license from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskType::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskType not found'));
        }

        // set the response
        $response = $taskStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskType found', $response);
    }

    /**
     * @OA\Put(
     *     path="/task-status/{id}",
     *     tags={"TaskType"},
     *     summary="Update TaskType by ID",
     *     description="Update TaskType by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskType to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaskType object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/TaskTypeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskType updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskType not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="TaskType already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateTaskType(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "TaskType 1",
        //     "description": "This is the first taskStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the TaskType from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskType::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the taskStatus is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskType not found'));
        }

        // get the TaskType data from the request body
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
                $this->request->handleErrorAndQuit(409, new Exception('TaskType already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskType updated');

    }


    /**
     * @OA\Delete(
     *     path="/task-status/{id}",
     *     tags={"TaskType"},
     *     summary="Delete TaskType by ID",
     *     description="Delete TaskType by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TaskType to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TaskType deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskType not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteTaskType(int $id): void
    {
        // get the TaskType from the database by its id
        try {
            $taskStatus = $this->dao->getOneBy(TaskType::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the TaskType is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskType not found'));
        }

        // remove the TaskType
        try {
            $this->dao->delete($taskStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskType deleted');
    }


}