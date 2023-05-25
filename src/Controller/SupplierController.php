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

/**
 * @OA\Schema (
 *     schema="SupplierRequest",
 *     required={"name", "firstName", "lastName", "email", "address", "city", "country", "zipCode", "phone", "company"},
 *     @OA\Property(property="name", type="string", example="Aubade"),
 *     @OA\Property(property="firstName", type="string", example="Jean"),
 *     @OA\Property(property="lastName", type="string", example="Dupont"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@aubade"),
 *     @OA\Property(property="address", type="string", example="1 rue de la lingerie"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="company", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="SupplierResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Aubade"),
 *     @OA\Property(property="firstName", type="string", example="Jean"),
 *     @OA\Property(property="lastName", type="string", example="Dupont"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@aubade"),
 *     @OA\Property(property="address", type="string", example="1 rue de la lingerie"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-03-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-03-01 00:00:00")
 * )
 *
 */
class SupplierController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'firstName', 'lastName', 'email', 'address', 'city', 'country', 'zipCode', 'phone', 'company'];


    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/supplier",
     *     tags={"Supplier"},
     *     summary="Add a new supplier",
     *     description="Add a new supplier to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Supplier object that needs to be added to the database",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Supplier already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addSupplier(): void
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
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
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
            $companyObject = $this->dao->getOneBy(Company::class, ['id' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // create a new Supplier
        $supplier = new Supplier($name, $firstName, $lastName, $email, $address, $city, $country, $zipCode, $phone, $companyObject);

        // flush the entity manager
        try {
            $this->dao->add($supplier);
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

    /**
     * @OA\Get(
     *     path="/supplier/all",
     *     tags={"Supplier"},
     *     summary="Get all suppliers",
     *     description="Returns all suppliers from the database",
     *     @OA\Response(
     *         response=200,
     *         description="Suppliers found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SupplierResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getSuppliers(): void
    {
        // get all the Supplier from the database
        try {
            $suppliers = $this->dao->getAll(Supplier::class);
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



    /**
     * @OA\Get(
     *     path="/supplier/{id}",
     *     tags={"Supplier"},
     *     summary="Get a supplier by ID",
     *     description="Returns a supplier from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the supplier to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier found",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getSupplierById(int $id): void
    {
        // get the supplier from the database by its id
        try {
            $supplier = $this->dao->getOneBy(Supplier::class, ['id' => $id]);
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

    /**
     * @OA\Put(
     *     path="/supplier/{id}",
     *     tags={"Supplier"},
     *     summary="Update a supplier by ID",
     *     description="Updates a supplier from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the supplier to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Supplier object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Supplier already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the supplier from the database by its id
        try {
            $supplier = $this->dao->getOneBy(Supplier::class, ['id' => $id]);
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
            $company = $this->dao->getOneBy(Company::class, ['id' => $company]);
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
            $this->dao->update($supplier);
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


    /**
     * @OA\Delete(
     *     path="/supplier/{id}",
     *     tags={"Supplier"},
     *     summary="Delete a supplier by ID",
     *     description="Deletes a supplier from the database by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the supplier to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteSupplier(int $id): void
    {
        // get the Supplier from the database by its id
        try {
            $supplier = $this->dao->getOneBy(Supplier::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Supplier is not found
        if (!$supplier) {
            $this->request->handleErrorAndQuit(404, new Exception('Supplier not found'));
        }

        // remove the Supplier
        try {
            $this->dao->delete($supplier);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Supplier deleted');
    }



}