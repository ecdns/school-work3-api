<?php

declare(strict_types=1);

namespace Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Estimate;
use Entity\EstimateStatus;
use Entity\Project;
use Entity\QuantityUnit;
use Entity\Role;
use Entity\Supplier;
use Entity\Vat;
use Exception;
use Service\DAO;
use Service\Request;

class EstimateController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'project', 'expiredAt', 'estimateStatus'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    public function addEstimate(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Estimate 1",
//             "description": "This is the first estimate",
//             "project": 1,
//             "expiredAt": "2021-09-30",
//             "estimateStatus": 1
//         }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the estimate data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $project = $requestBody['project'];
        $expiredAt = $requestBody['expiredAt'];
        $estimateStatus = $requestBody['estimateStatus'];

        // get the estimate from the database by its id
        try {
            $projectObject = $this->dao->getOneEntityBy(Project::class, ['id' => $project]);
            $estimateStatusObject = $this->dao->getOneEntityBy(EstimateStatus::class, ['id' => $estimateStatus]);
            $expiredAt = DateTime::createFromFormat('Y-m-d', $expiredAt);


            if (!$estimateStatusObject || !$projectObject || !$expiredAt) {
                $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus, Project, ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }



        // create a new estimate
        $estimate = new Estimate($name, $description, $projectObject, $expiredAt, $estimateStatusObject);

        // add the estimate to the database
        try {
            $this->dao->addEntity($estimate);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Estimate already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Estimate created');
    }

    public function getEstimates(): void
    {
        // get all roles
        try {
            //get all estimates
            $estimates = $this->dao->getAllEntities(Estimate::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }

    public function getEstimatesByProject(int $id): void
    {
        // get all roles
        try {
            //get all estimates by company
            $estimates = $this->dao->getEntitiesBy(Estimate::class, ['project' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }

    public function getEstimateById(int $id): void
    {
        // get the role by id
        try {
            $estimate = $this->dao->getOneEntityBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // set the response
        $response = $estimate->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate found', $response);
    }

    public function updateEstimate(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the estimate by id
        try {
            $estimate = $this->dao->getOneEntityBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the estimate is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // it will look like this:
        // {
        //     "name": "Estimate 1",
        //     "description": "This is the first estimate",
        //     "project": 1,
        //     "expiredAt": "2021-09-30",
        //     "estimateStatus": 1
        // }


        // get the estimate data from the request body
        $name = $requestBody['name'] ?? $estimate->getName();
        $description = $requestBody['description'] ?? $estimate->getDescription();
        $project = $requestBody['project'] ?? $estimate->getProject()->getId();
        $expiredAt = $requestBody['expiredAt'] ?? $estimate->getExpiredAt();
        $estimateStatus = $requestBody['estimateStatus'] ?? $estimate->getEstimateStatus()->getId();

        try {

            $projectObject = $this->dao->getOneEntityBy(Project::class, ['id' => $project]);
            $estimateStatusObject = $this->dao->getOneEntityBy(EstimateStatus::class, ['id' => $estimateStatus]);
            $expiredAt = DateTime::createFromFormat('Y-m-d', $expiredAt);

            if (!$estimateStatusObject || !$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus, Project or ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the estimate
        $estimate->setName($name);
        $estimate->setDescription($description);
        $estimate->setProject($projectObject);
        $estimate->setExpiredAt($expiredAt);
        $estimate->setEstimateStatus($estimateStatusObject);

        // update the estimate in the database
        try {
            $this->dao->updateEntity($estimate);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Estimate already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate updated');

    }

    public function deleteEstimate(int $id): void
    {
        // get the estimate by id
        try {
            $estimate = $this->dao->getOneEntityBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the estimate is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // remove the estimate
        try {
            $this->dao->deleteEntity($estimate);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate deleted');
    }
}