<?php

namespace Controller;

// controller for entity EstimateStatus
use Doctrine\ORM\EntityManager;
use Entity\EstimateStatus;
use Exception;
use Service\DAO;
use Service\Request;

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

    public function addEstimateStatus(): void
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
            $this->dao->addEntity($estimateStatus);
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

    //function for getting all EstimateStatus
    public function getEstimateStatuses(): void
    {
        // get all the EstimateStatus from the database
        try {
            $productFamilies = $this->dao->getAllEntities(EstimateStatus::class);
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

    public function getEstimateStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $estimateStatus = $this->dao->getOneEntityBy(EstimateStatus::class, ['id' => $id]);
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

    //function for updating a estimateStatus
    public function updateEstimateStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "EstimateStatus 1",
        //     "description": "This is the first estimateStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the EstimateStatus from the database by its id
        try {
            $estimateStatus = $this->dao->getOneEntityBy(EstimateStatus::class, ['id' => $id]);
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
            $this->dao->updateEntity($estimateStatus);
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

    //function for deleting a EstimateStatus
    public function deleteEstimateStatus(int $id): void
    {
        // get the EstimateStatus from the database by its id
        try {
            $estimateStatus = $this->dao->getOneEntityBy(EstimateStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the EstimateStatus is not found
        if (!$estimateStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus not found'));
        }

        // remove the EstimateStatus
        try {
            $this->dao->deleteEntity($estimateStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'EstimateStatus deleted');
    }

}