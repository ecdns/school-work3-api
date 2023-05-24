<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Customer;
use Entity\Project;
use Entity\Task;
use Entity\TaskStatus;
use Entity\User;
use Exception;
use Service\DAO;
use Service\Request;

class TaskController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;

    private const REQUIRED_FIELDS = ['title', 'description', 'location', 'dueDate', 'project', 'user', 'taskStatus'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/task",
     *     tags={"Task"},
     *     summary="Add a new task",
     *     description="Add a new task to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task object that needs to be added to the database",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User, Project or TaskStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Task already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addTask(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this
//         {
//             "title": "Task 1",
//             "description": "This is the first task",
//             "location": "Location 1",
//             "dueDate": "2021-01-01",
//             "project": 1,
//             "user": 1,
//             "taskStatus": 1
//         }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the task data from the request body
        $name = $requestBody['title'];
        $description = $requestBody['description'];
        $location = $requestBody['location'];
        $dueDate = $requestBody['dueDate'];
        $project = $requestBody['project'];
        $user = $requestBody['user'];
        $taskStatus = $requestBody['taskStatus'];


        // get the task FK from the database by its id
        try {
            $projectObject = $this->dao->getOneEntityBy(Project::class, ['id' => $project]);
            $userObject = $this->dao->getOneEntityBy(User::class, ['id' => $user]);
            $taskStatusObject = $this->dao->getOneEntityBy(TaskStatus::class, ['id' => $taskStatus]);

            if (!$projectObject || !$userObject || !$taskStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('User, Project or TaskStatus not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new task
        $task = new Task($name, $description, $location, $dueDate, $projectObject, $userObject, $taskStatusObject);

        // add the Task to the database
        try {
            $this->dao->addEntity($task);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Task already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Task created');
    }



    /**
     * Get all tasks
     *
     * @OA\Get(
     *     path="/task/all",
     *     tags={"Tasks"},
     *     summary="Get all tasks",
     *     description="Returns all tasks",
     *     @OA\Response(
     *         response=200,
     *         description="Tasks found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTasks(): void
    {
        // get all tasks
        try {
            //get all Tasks
            $tasks = $this->dao->getAllEntities(Task::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($tasks as $task) {
            $response[] = $task->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Tasks found', $response);
    }




    /**
     * Get all tasks by user
     *
     * @OA\Get(
     *     path="/task/user/{id}",
     *     tags={"Tasks"},
     *     summary="Get all tasks by user",
     *     description="Returns all tasks assigned to a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTasksByUser(int $id): void
    {
        // get all roles
        try {
            //get all task by company
            $tasks = $this->dao->getEntitiesBy(Task::class, ['user' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($tasks as $task) {
            $response[] = $task->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Tasks found', $response);
    }



    /**
     * Get all tasks by project
     *
     * @OA\Get(
     *     path="/task/project/{id}",
     *     tags={"Tasks"},
     *     summary="Get all tasks by project",
     *     description="Returns all tasks assigned to a project",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTasksByProject(int $id): void
    {
        // get all tasks
        try {
            //get all task by company
            $tasks = $this->dao->getEntitiesBy(Task::class, ['project' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($tasks as $task) {
            $response[] = $task->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Tasks found', $response);
    }



    /**
     * Get task by id
     *
     * @OA\Get(
     *     path="/task/{id}",
     *     tags={"Tasks"},
     *     summary="Get task by id",
     *     description="Returns a task by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task found",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTaskById(int $id): void
    {
        // get the task by id
        try {
            $task= $this->dao->getOneEntityBy(Task::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Task is not found
        if (!$task) {
            $this->request->handleErrorAndQuit(404, new Exception('Task not found'));
        }

        // set the response
        $response = $task->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Task found', $response);
    }


    /**
     * Update a task by ID
     *
     * @OA\Put(
     *     path="/task/{id}",
     *     summary="Update a task by ID",
     *     description="Update a task by ID",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the task to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Task already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateTask(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the Task by id
        try {
            $task = $this->dao->getOneEntityBy(Task::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Task is not found
        if (!$task) {
            $this->request->handleErrorAndQuit(404, new Exception('Task not found'));
        }

        // it will look like this:
        // {
        //     "title": "Task 1",
        //     "description": "This is the first task",
        //     "location": "Location 1",
        //     "dueDate": "2021-01-01",
        //     "project": 1,
        //     "user": 1,
        //     "taskStatus": 1
        // }

        // get the Task data from the request body
        $name = $requestBody['title'] ?? $task->getName();
        $description = $requestBody['description'] ?? $task->getDescription();
        $location = $requestBody['location'] ?? $task->getLocation();
        $dueDate = $requestBody['dueDate'] ?? $task->getDueDate();
        $project = $requestBody['project'] ?? $task->getProject();
        $user = $requestBody['user'] ?? $task->getUser();
        $taskStatus = $requestBody['taskStatus'] ?? $task->getTaskStatus();


        try {
            $projectObject = $this->dao->getOneEntityBy(Project::class, ['id' => $project]);
            $userObject = $this->dao->getOneEntityBy(User::class, ['id' => $user]);
            $taskStatusObject = $this->dao->getOneEntityBy(TaskStatus::class, ['id' => $taskStatus]);

            if (!$projectObject || !$userObject || !$taskStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('User, Project or TaskStatus not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the task
        $task->setTitle($name);
        $task->setDescription($description);
        $task->setLocation($location);
        $task->setDueDate($dueDate);
        $task->setProject($projectObject);
        $task->setUser($userObject);
        $task->setTaskStatus($taskStatusObject);

        // update the task in the database
        try {
            $this->dao->updateEntity($task);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Task already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Task updated');

    }

    /**
     * Deletes a task by ID
     *
     * @OA\Delete(
     *     path="/task/{id}",
     *     tags={"Tasks"},
     *     summary="Deletes a task by ID",
     *     description="Deletes a task by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the task to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteTask(int $id): void
    {
        // get the Task by id
        try {
            $task = $this->dao->getOneEntityBy(Task::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Task is not found
        if (!$task) {
            $this->request->handleErrorAndQuit(404, new Exception('Task not found'));
        }

        // remove the Task
        try {
            $this->dao->deleteEntity($task);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Task deleted');
    }
}