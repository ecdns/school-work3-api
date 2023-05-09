<?php

namespace Controller;

// controller for entity Supplier
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\License;
use Entity\ProductFamily;
use Entity\Supplier;
use Exception;
use Service\Request;

class SupplierController extends AbstractController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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

        try {
            $companyObject = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $company]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // create a new Supplier
        $supplier = new Supplier($name, $firstName, $lastName, $email, $address, $city, $country, $zipCode, $phone, $companyObject);

        // persist the Supplier
        try {
            $this->entityManager->persist($supplier);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Supplier already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'Supplier created');

    }

    //function for getting all Suppliers
    public function getSuppliers(): void
    {
        // get all the Supplier from the database
        try {
            $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($suppliers as $supplier) {
            $response[] = $supplier->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Supplier found', $response);
    }

    public function getSupplierById(int $id): void
    {
        // get the supplier from the database by its id
        try {
            $supplier = $this->entityManager->getRepository(Supplier::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$supplier) {
            Request::handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // set the response
        $response = $supplier->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'Supplier found', $response);
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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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

        // get the ProductFamily from the database by its id
        try {
            $supplier = $this->entityManager->getRepository(Supplier::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        try {
            $companyObject = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $company]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the productFamily is not found
        if (!$supplier) {
            Request::handleErrorAndQuit(404, new Exception('Supplier not found'));
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
        $supplier->setCompany($companyObject);

        // persist the productFamily
        try {
            $this->entityManager->persist($supplier);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Supplier already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Supplier updated');

    }

    //function for deleting a Supplier
    public function deleteSupplier(int $id): void
    {
        // get the Supplier from the database by its id
        try {
            $supplier = $this->entityManager->getRepository(Supplier::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the Supplier is not found
        if (!$supplier) {
            Request::handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // remove the Supplier
        try {
            $this->entityManager->remove($supplier);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Supplier deleted');
    }


}