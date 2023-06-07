<?php

declare(strict_types=1);

namespace Controller;

use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\User;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="CustomerRequest",
 *     required={"firstName", "lastName", "email", "address", "city", "country", "zipCode", "phone", "company", "status"},
 *     @OA\Property(property="firstName", type="string", example="John"),
 *     @OA\Property(property="lastName", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john-doe@gmail.com"),
 *     @OA\Property(property="address", type="string", example="1, rue de la Paix"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="company", type="integer", example="1"),
 *     @OA\Property(property="status", type="integer", example="1")
 * )
 * @OA\Schema (
 *     schema="CustomerResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="firstName", type="string", example="John"),
 *     @OA\Property(property="lastName", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john-doe@gmail.com"),
 *     @OA\Property(property="address", type="string", example="1, rue de la Paix"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *     @OA\Property(property="status", type="object", ref="#/components/schemas/CustomerStatusResponse"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class CustomerController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name','firstName', 'lastName', 'email', 'job', 'address', 'city', 'country', 'zipCode', 'phone', 'company', 'status'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/customer",
     *     tags={"Customer"},
     *     summary="Add a new customer",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Customer created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company, User or CustomerStatus not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Customer already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function addCustomer(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "firstName": "John",
//             "lastName": "Doe",
//             "email": "john.doe@example",
//             "address": "John Doe Street 1",
//             "city": "John Doe City",
//             "country": "John Doe Country",
//             "zipCode": "12345",
//             "phone": "123456789",
//             "company": 1,
//             "user": 1,
//             "status": 1
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // check if the data is valid
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $name = $requestBody['name'];
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $job = $requestBody['job'];
        $address = $requestBody['address'];
        $city = $requestBody['city'];
        $country = $requestBody['country'];
        $zipCode = $requestBody['zipCode'];
        $phone = $requestBody['phone'];
        $company = $requestBody['company'];
        $status = $requestBody['status'];

        // get the company from the database by its id
        try {
            $companyObject = $this->dao->getOneBy(Company::class, ['id' => $company]);
            $customerStatusObject = $this->dao->getOneBy(CustomerStatus::class, ['id' => $status]);

            if (!$companyObject || !$customerStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User or CustomerStatus not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new Customer
        $customer = new Customer($name, $firstName, $lastName, $email, $job, $address, $city, $country, $zipCode, $phone, $companyObject, $customerStatusObject);

        // add the customer to the database
        try {
            $this->dao->add($customer);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Customer already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(201, 'Customer created');

    }

    /**
     * @OA\Get(
     *     path="/customer/all",
     *     tags={"Customer"},
     *     summary="Get all customers",
     *     @OA\Response(
     *         response="200",
     *         description="Customers found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getCustomers(): void
    {
        // get the customers from the database
        try {
            $companies = $this->dao->getAll(Customer::class);
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

    /**
     * @OA\Get(
     *     path="/customer/{id}",
     *     tags={"Customer"},
     *     summary="Get customer by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Customer found"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getCustomerById(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // construct the response with the customer data
        $response = $customer->toArray();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer found', $response);

    }

    /**
     * @OA\Get(
     *     path="/customer/company/{id}",
     *     tags={"Customer"},
     *     summary="Get customers by company id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company to retrieve customers from",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Customers found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Customers not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    //get by company id
    public function getCustomerByCompany(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customers = $this->dao->getBy(Customer::class, ['company' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customers) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // construct the response with the companies data
        $response = [];
        foreach ($customers as $customer) {
            $response[] = $customer->toArray();
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer found', $response);

    }


    /**
     * @OA\Put(
     *     path="/customer/{id}",
     *     tags={"Customer"},
     *     summary="Update a customer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Customer data to update",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Customer updated successfully"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Customer already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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
            $customer = $this->dao->getOneBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // get the controller data from the request body
        $name = $requestBody['name'] ?? $customer->getName();
        $firstName = $requestBody['firstName'] ?? $customer->getFirstName();
        $lastName = $requestBody['lastName'] ?? $customer->getLastName();
        $email = $requestBody['email'] ?? $customer->getEmail();
        $job = $requestBody['job'] ?? $customer->getJob();
        $address = $requestBody['address'] ?? $customer->getAddress();
        $city = $requestBody['city'] ?? $customer->getCity();
        $country = $requestBody['country'] ?? $customer->getCountry();
        $zipCode = $requestBody['zipCode'] ?? $customer->getZipCode();
        $phone = $requestBody['phone'] ?? $customer->getPhone();
        $company = $requestBody['company'] ?? $customer->getCompany()->getId();
        $status = $requestBody['status'] ?? $customer->getStatus()->getId();

        // get the controller from the database by its name
        try {
            $companyObject = $this->dao->getOneBy(Company::class, ['id' => $company]);
            $customerStatusObject = $this->dao->getOneBy(CustomerStatus::class, ['id' => $status]);

            if (!$companyObject || !$customerStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User or CustomerStatus not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // update the customer data checking if the data has been changed
        $customer->setName($name);
        $customer->setFirstName($firstName);
        $customer->setLastName($lastName);
        $customer->setEmail($email);
        $customer->setJob($job);
        $customer->setAddress($address);
        $customer->setCity($city);
        $customer->setCountry($country);
        $customer->setZipCode($zipCode);
        $customer->setPhone($phone);
        $customer->setCompany($companyObject);
        $customer->setStatus($customerStatusObject);

        // update the customer
        try {
            $this->dao->update($customer);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Customer already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Customer updated successfully');

    }

    /**
     * @OA\Delete(
     *     path="/customer/{id}",
     *     tags={"Customer"},
     *     summary="Delete a customer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Customer deleted successfully"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteCustomer(int $id): void
    {
        // get the customer from the database by its id
        try {
            $customer = $this->dao->getOneBy(Customer::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the customer is not found, return an error
        if (!$customer) {
            $this->request->handleErrorAndQuit(404, new Exception('Customer not found'));
        }

        // remove the customer
        try {
            $this->dao->delete($customer);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Customer deleted successfully');

    }
}

