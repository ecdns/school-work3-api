<?php

namespace Controller;

// controller for entity CustomerStatus
use Doctrine\ORM\EntityManager;
use Entity\CustomerStatus;
use Exception;
use Service\DAO;
use Service\Request;

class CustomerStatusController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description'];
    
    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    public function addCustomerStatus(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "CustomerStatus 1",
//             "description": "This is the first customerStatus"
//         }

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
            $this->dao->addEntity($customerStatus);
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

    //function for getting all CustomerStatus
    public function getCustomerStatuses(): void
    {
        // get all the CustomerStatus from the database
        try {
            $productFamilies = $this->dao->getAllEntities(CustomerStatus::class);
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

    public function getCustomerStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $customerStatus = $this->dao->getOneEntityBy(CustomerStatus::class, ['id' => $id]);
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

    //function for updating a customerStatus
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
            $customerStatus = $this->dao->getOneEntityBy(CustomerStatus::class, ['id' => $id]);
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
            $this->dao->updateEntity($customerStatus);
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

    //function for deleting a CustomerStatus
    public function deleteCustomerStatus(int $id): void
    {
        // get the CustomerStatus from the database by its id
        try {
            $customerStatus = $this->dao->getOneEntityBy(CustomerStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the CustomerStatus is not found
        if (!$customerStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('CustomerStatus not found'));
        }

        // remove the CustomerStatus
        try {
            $this->dao->deleteEntity($customerStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'CustomerStatus deleted');
    }

}