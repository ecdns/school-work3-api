<?php

declare(strict_types=1);

namespace Controller;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\License;
use Entity\ProjectStatus;
use Entity\User;
use Exception;
use Service\DAO;
use Service\Request;

class CustomerController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['firstName', 'lastName', 'email', 'address', 'city', 'country', 'zipCode', 'phone', 'company', 'user', 'status'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    public function addCustomer(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "firstName": "John",
        //     "lastName": "Doe",
        //     "email": "john.doe@example",
        //     "address": "John Doe Street 1",
        //     "city": "John Doe City",
        //     "country": "John Doe Country",
        //     "zipCode": "12345",
        //     "phone": "123456789",
        //     "company": 1,
        //     "user": 1,
        //     "status": 1
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // check if the data is valid
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $address = $requestBody['address'];
        $city = $requestBody['city'];
        $country = $requestBody['country'];
        $zipCode = $requestBody['zipCode'];
        $phone = $requestBody['phone'];
        $company = $requestBody['company'];
        $user = $requestBody['user'];
        $status = $requestBody['status'];

        // get the company from the database by its id
        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
            $userObject = $this->dao->getOneEntityBy(User::class, ['id' => $user]);
            $customerStatusObject = $this->dao->getOneEntityBy(CustomerStatus::class, ['id' => $status]);

            if (!$companyObject || !$userObject || !$customerStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User or CustomerStatus not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new Customer
        $customer = new Customer( $firstName, $lastName, $email, $address, $city, $country, $zipCode, $phone, $companyObject, $userObject, $customerStatusObject);

        // add the customer to the database
        try {
            $this->dao->addEntity($customer);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Customer already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(201, 'Customer created');

    }

    public function getCustomers(): void
    {
        // get the customers from the database
        try {
            $companies = $this->dao->getAllEntities(Customer::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // construct the response with the companies data
        $response = [];
        foreach ($companies as $customer) {
            $response[] = $customer->toArray();
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customers found', $response);

    }

    public function getCustomerById(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneEntityBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // construct the response with the customer data
        $response = $customer->toFullArrayWithUsers();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer found', $response);

    }

    //get by company id
    public function getCustomerByCompany(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneEntityBy(Customer::class, ['company' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // construct the response with the customer data
        $response = $customer->toFullArrayWithUsers();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer found', $response);

    }

    public function updateCustomer(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Cube 3",
        //     "address": "1, rue de la paix",
        //     "city": "Paris",
        //     "country": "France",
        //     "zipCode": "75000",
        //     "phone": "0123456789",
        //     "slogan": "The best customer ever",
        //     "logoPath": "cube3.png",
        //     "license": basic,
        //     "language": "fr"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the request body
        if ($this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS) === false) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneEntityBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // get the controller data from the request body
        $firstName = $requestBody['firstName'] ?? $customer->getFirstName();
        $lastName = $requestBody['lastName'] ?? $customer->getLastName();
        $address = $requestBody['address'] ?? $customer->getAddress();
        $city = $requestBody['city'] ?? $customer->getCity();
        $country = $requestBody['country'] ?? $customer->getCountry();
        $zipCode = $requestBody['zipCode'] ?? $customer->getZipCode();
        $phone = $requestBody['phone'] ?? $customer->getPhone();
        $company = $requestBody['company'] ?? $customer->getCompany()->getId();
        $user = $requestBody['user'] ?? $customer->getUser()->getId();
        $status = $requestBody['status'] ?? $customer->getCustomerStatus()->getId();

        // get the controller from the database by its name
        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
            $userObject = $this->dao->getOneEntityBy(User::class, ['id' => $user]);
            $customerStatusObject = $this->dao->getOneEntityBy(CustomerStatus::class, ['id' => $status]);

            if (!$companyObject || !$userObject || !$customerStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User or CustomerStatus not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // update the customer data checking if the data has been changed
        $customer->setFirstName($firstName);
        $customer->setLastName($lastName);
        $customer->setAddress($address);
        $customer->setCity($city);
        $customer->setCountry($country);
        $customer->setZipCode($zipCode);
        $customer->setPhone($phone);
        $customer->setCompany($companyObject);
        $customer->setUser($userObject);
        $customer->setCustomerStatus($customerStatusObject);

        // update the customer
        try {
            $this->dao->updateEntity($customer);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Customer already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer updated successfully');

    }

    public function deleteCustomer(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneEntityBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // remove the customer
        try {
            $this->dao->deleteEntity($customer);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Customer deleted successfully');

    }
}

