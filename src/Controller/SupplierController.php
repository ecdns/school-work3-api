<?php

namespace Controller;

// controller for entity Supplier
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\License;
use Entity\ProductFamily;
use Entity\Supplier;
use Exception;
use Service\DAO;
use Service\Request;

class SupplierController extends AbstractController
{

    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    //function for adding a new Supplier
    const REQUIRED_FIELDS = ['name', 'firstName', 'lastName', 'email', 'address', 'city', 'country', 'zipCode', 'phone', 'company'];

    public function addSupplier(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Aubade",
        //     "firstName": "Jean",
        //     "lastName": "Dupont",
        //     "email": "jean.dupont@aubade",
        //     "address": "1 rue de la lingerie",
        //     "city": "Paris",
        //     "country": "France",
        //     "zipCode": "75000",
        //     "phone": "0123456789",
        //     "company": 1
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the Supplier data from the request body
        $name = $requestBody['name'];
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $address = $requestBody['address'];
        $city = $requestBody['city'];
        $country = $requestBody['country'];
        $zipCode = $requestBody['zipCode'];
        $phone = $requestBody['phone'];
        $company = $requestBody['company'];

        // get the company from the database by its name
        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['name' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // create a new Supplier
        $supplier = new Supplier($name, $firstName, $lastName, $email, $address, $city, $country, $zipCode, $phone, $companyObject);

        // flush the entity manager
        try {
            $this->dao->addEntity($supplier);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Supplier already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Supplier created');

    }

    //function for getting all Suppliers
    public function getSuppliers(): void
    {
        // get all the Supplier from the database
        try {
            $suppliers = $this->dao->getAllEntities(Supplier::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($suppliers as $supplier) {
            $response[] = $supplier->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Supplier found', $response);
    }

    public function getSupplierById(int $id): void
    {
        // get the supplier from the database by its id
        try {
            $supplier = $this->dao->getOneEntityBy(Supplier::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$supplier) {
            $this->request->handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // set the response
        $response = $supplier->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Supplier found', $response);
    }

    //function for updating a supplier
    public function updateSupplier(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Aubade",
//             "firstName": "Jean",
//             "lastName": "Dupont",
//             "email": "jean.dupont@aubade",
//             "address": "1 rue de la lingerie",
//             "city": "Paris",
//             "country": "France",
//             "zipCode": "75000",
//             "phone": "0123456789",
//             "company": 1
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the supplier from the database by its id
        try {
            $supplier = $this->dao->getOneEntityBy(Supplier::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the productFamily is not found
        if (!$supplier) {
            $this->request->handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // get the Supplier data from the request body
        $name = $requestBody['name'] ?? $supplier->getName();
        $firstName = $requestBody['firstName'] ?? $supplier->getFirstName();
        $lastName = $requestBody['lastName'] ?? $supplier->getLastName();
        $email = $requestBody['email'] ?? $supplier->getEmail();
        $address = $requestBody['address'] ?? $supplier->getAddress();
        $city = $requestBody['city'] ?? $supplier->getCity();
        $country = $requestBody['country'] ?? $supplier->getCountry();
        $zipCode = $requestBody['zipCode'] ?? $supplier->getZipCode();
        $phone = $requestBody['phone'] ?? $supplier->getPhone();
        $company = $requestBody['company'] ?? $supplier->getCompany()->getId();

        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // update the productFamily
        $supplier->setName($name);
        $supplier->setFirstName($firstName);
        $supplier->setLastName($lastName);
        $supplier->setEmail($email);
        $supplier->setAddress($address);
        $supplier->setCity($city);
        $supplier->setCountry($country);
        $supplier->setZipCode($zipCode);
        $supplier->setPhone($phone);
        $supplier->setCompany($company);

        // flush the entity manager
        try {
            $this->dao->updateEntity($supplier);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Supplier already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Supplier updated');

    }

    //function for deleting a Supplier
    public function deleteSupplier(int $id): void
    {
        // get the Supplier from the database by its id
        try {
            $supplier = $this->dao->getOneEntityBy(Supplier::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Supplier is not found
        if (!$supplier) {
            $this->request->handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // remove the Supplier
        try {
            $this->dao->deleteEntity($supplier);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Supplier deleted');
    }


}