<?php

namespace Controller;

// controller for entity TaskStatus
use Doctrine\ORM\EntityManager;
use Entity\TaskStatus;
use Exception;
use Service\DAO;
use Service\Request;

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
            $this->dao->addEntity($taskStatus);
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

    //function for getting all TaskStatus
    public function getTaskStatuses(): void
    {
        // get all the TaskStatus from the database
        try {
            $productFamilies = $this->dao->getAllEntities(TaskStatus::class);
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

    public function getTaskStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $taskStatus = $this->dao->getOneEntityBy(TaskStatus::class, ['id' => $id]);
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

    //function for updating a taskStatus
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
            $taskStatus = $this->dao->getOneEntityBy(TaskStatus::class, ['id' => $id]);
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
            $this->dao->updateEntity($taskStatus);
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

    //function for deleting a TaskStatus
    public function deleteTaskStatus(int $id): void
    {
        // get the TaskStatus from the database by its id
        try {
            $taskStatus = $this->dao->getOneEntityBy(TaskStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the TaskStatus is not found
        if (!$taskStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('TaskStatus not found'));
        }

        // remove the TaskStatus
        try {
            $this->dao->deleteEntity($taskStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'TaskStatus deleted');
    }

}