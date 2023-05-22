<?php

namespace Controller;

// controller for entity ProjectStatus
use Doctrine\ORM\EntityManager;
use Entity\ProjectStatus;
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
//             "name": "ProjectStatus 1",
//             "description": "This is the first projectStatus"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProjectStatus data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new projectStatus
        $projectStatus = new TaskStatus($name, $description);

        // persist the projectStatus
        try {
            $this->dao->addEntity($projectStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('TaskStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'ProjectStatus created');

    }

    //function for getting all ProjectStatus
    public function getProjectStatuses(): void
    {
        // get all the ProjectStatus from the database
        try {
            $productFamilies = $this->dao->getAllEntities(ProjectStatus::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $projectStatus) {
            $response[] = $projectStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus found', $response);
    }

    public function getProjectStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $projectStatus = $this->dao->getOneEntityBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // set the response
        $response = $projectStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus found', $response);
    }

    //function for updating a projectStatus
    public function updateProjectStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "ProjectStatus 1",
        //     "description": "This is the first projectStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProjectStatus from the database by its id
        try {
            $projectStatus = $this->dao->getOneEntityBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the projectStatus is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // get the ProjectStatus data from the request body
        $name = $requestBody['name'] ?? $projectStatus->getName();
        $description = $requestBody['description'] ?? $projectStatus->getDescription();

        // update the projectStatus
        $projectStatus->setName($name);
        $projectStatus->setDescription($description);

        // persist the projectStatus
        try {
            $this->dao->updateEntity($projectStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('ProjectStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus updated');

    }

    //function for deleting a ProjectStatus
    public function deleteProjectStatus(int $id): void
    {
        // get the ProjectStatus from the database by its id
        try {
            $projectStatus = $this->dao->getOneEntityBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the ProjectStatus is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // remove the ProjectStatus
        try {
            $this->dao->deleteEntity($projectStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus deleted');
    }

}