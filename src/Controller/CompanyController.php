<?php

declare(strict_types=1);

namespace Controller;

use DateInterval;
use DateTime;
use Entity\Company;
use Entity\License;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="CompanyRequest",
 *     required={"name", "address", "city", "country", "zipCode", "phone", "slogan", "logoPath", "license", "language"},
 *     @OA\Property(property="name", type="string", example="Cube 3"),
 *     @OA\Property(property="address", type="string", example="1, rue de la paix"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="slogan", type="string", example="The best company ever"),
 *     @OA\Property(property="logoPath", type="string", example="cube3.png"),
 *     @OA\Property(property="license", type="integer", example="1"),
 *     @OA\Property(property="language", type="string", example="fr")
 * )
 * @OA\Schema (
 *     schema="CompanyResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Cube 3"),
 *     @OA\Property(property="address", type="string", example="1, rue de la paix"),
 *     @OA\Property(property="city", type="string", example="Paris"),
 *     @OA\Property(property="country", type="string", example="France"),
 *     @OA\Property(property="zipCode", type="string", example="75000"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="slogan", type="string", example="The best company ever"),
 *     @OA\Property(property="logoPath", type="string", example="cube3.png"),
 *     @OA\Property(property="license", type="object", ref="#/components/schemas/LicenseResponse"),
 *     @OA\Property(property="language", type="string", example="fr"),
 *     @OA\Property(property="licenseExpirationDate", type="datetime", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 * @OA\Schema (
 *     schema="TotalByMonthResponse",
 *     @OA\Property(property="06-2020", type="string", format="date-time", example="3656.00"),
 *     @OA\Property(property="07-2020", type="string", format="date-time", example="3756.00"),
 *     @OA\Property(property="08-2020", type="string", format="date-time", example="3856.00"),
 *     @OA\Property(property="09-2020", type="string", format="date-time", example="3956.00"),
 * )
 */
class CompanyController extends AbstractController
{
    private const REQUIRED_FIELDS = ['name', 'address', 'city', 'country', 'zipCode', 'phone', 'slogan', 'logoPath', 'license', 'language'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/company",
     *     tags={"Company"},
     *     summary="Add a new company",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CompanyRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Company created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="License not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Company already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function addCompany(): void
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
        //     "slogan": "The best company ever",
        //     "logoPath": "cube3.png",
        //     "license": basic,
        //     "language": "fr"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // check if the data is valid
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $name = $requestBody['name'];
        $address = $requestBody['address'];
        $city = $requestBody['city'];
        $country = $requestBody['country'];
        $zipCode = $requestBody['zipCode'];
        $phone = $requestBody['phone'];
        $slogan = $requestBody['slogan'];
        $logoPath = $requestBody['logoPath'];
        $licenseId = $requestBody['license'];
        $language = $requestBody['language'];

        // get the license from the database by its name
        try {
            $license = $this->dao->getOneBy(License::class, ['id' => $licenseId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$license) {
            $this->request->handleErrorAndQuit(404, new Exception('License not found'));
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            // if the error mentions a constraint violation, it means the license already exists
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('License already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // check if the license is expired, if it is, return set $isEnable to false
        $isEnabled = true;
        if ($licenseExpirationDate < new DateTime()) {
            $isEnabled = false;
        }

        // create a new Company
        $company = new Company($name, $address, $city, $country, $zipCode, $phone, $slogan, $logoPath, $license, $licenseExpirationDate, $language, $isEnabled);

        // add the company to the database
        try {
            $this->dao->add($company);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(201, 'Company created');

    }


    /**
     * @OA\Get(
     *     path="/company/all",
     *     tags={"Company"},
     *     summary="Get all companies",
     *     description="Returns a list of all companies",
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CompanyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getCompanies(): void
    {
        // get the companies from the database
        try {
            $companies = $this->dao->getAll(Company::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // construct the response with the companies data
        $response = [];
        foreach ($companies as $company) {
            $response[] = $company->toArray();
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Companies found', $response);

    }


    /**
     * @OA\Get(
     *     path="/company/{id}",
     *     tags={"Company"},
     *     summary="Get a company by ID",
     *     description="Returns a company by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getCompanyById(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Company found', $response);

    }


    /**
     * @OA\Get(
     *     path="/company/{name}",
     *     tags={"Company"},
     *     summary="Get a company by name",
     *     description="Returns a company by name",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of the company to return",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getCompanyByName(string $name): void
    {
        // get the company from the database by its name
        try {
            $company = $this->dao->getOneBy(Company::class, ['name' => $name]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Company found', $response);

    }

    /**
     * @OA\Get(
     *     path="/company/{companyId}/totalAmountByMonth",
     *     tags={"Company"},
     *     summary="Get total amount by month for a company",
     *     description="Returns the total amount by month for a company",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="ID of the company to get the total amount by month for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTotalAmountByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalAmountByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }

    /**
     * @OA\Get(
     *     path="/company/{companyId}/totalAmountWithVatByMonth",
     *     tags={"Company"},
     *     summary="Get total amount with VAT by month for a company",
     *     description="Returns the total amount with VAT by month for a company",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="ID of the company to get the total amount with VAT by month for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTotalAmountWithVatByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalAmountWithVatByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }

    /**
     * @OA\Get(
     *     path="/company/{companyId}/totalBuyPriceByMonth",
     *     tags={"Company"},
     *     summary="Get total buy price by month for a company",
     *     description="Returns the total buy price by month for a company",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="ID of the company to get the total buy price by month for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTotalBuyPriceByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalBuyPriceByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }

    /**
     * @OA\Get(
     *     path="/company/{companyId}/totalBuyPriceWithVatByMonth",
     *     tags={"Company"},
     *     summary="Get total buy price with VAT by month for a company",
     *     description="Returns the total buy price with VAT by month for a company",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="ID of the company to get the total buy price with VAT by month for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTotalBuyPriceWithVatByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalBuyPriceWithVatByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }

    /**
     * @OA\Get(
     *     path="/company/{companyId}/totalProfitByMonth",
     *     tags={"Company"},
     *     summary="Get total profit by month for a company",
     *     description="Returns the total profit by month for a company",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="ID of the company to get the total profit by month for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getTotalProfitByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalProfitByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }

    /**
    * @OA\Get(
    *     path="/company/{companyId}/totalProfitWithVatByMonth",
    *     tags={"Company"},
    *     summary="Get total profit with VAT by month for a company",
    *     description="Returns the total profit with VAT by month for a company",
    *     @OA\Parameter(
    *         name="companyId",
    *         in="path",
    *         description="ID of the company to get the total profit with VAT by month for",
    *         required=true,
    *         @OA\Schema(
    *             type="integer"
    *         )
    *     ),
    *     @OA\Response(
    *         response="200",
    *         description="Successful operation",
    *         @OA\JsonContent(ref="#/components/schemas/TotalByMonthResponse")
    *     ),
    *     @OA\Response(
    *         response="404",
    *         description="Company not found"
    *     ),
    *     @OA\Response(
    *         response="500",
    *         description="Internal server error"
    *     )
    * )
    */
    public function getTotalProfitWithVatByMonth(int $companyId): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // construct the response with the company data
        $response = $company->getTotalProfitWithVatByMonth();

        // set the response
        $this->request->handleSuccessAndQuit(200, 'OK', $response);

    }
    
    /**
     * @OA\Put(
     *     path="/company/{id}",
     *     tags={"Company"},
     *     summary="Update a company by id",
     *     description="Updates a company by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of the company to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Company data to update",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateCompany(int $id): void
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
        //     "slogan": "The best company ever",
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

        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // get the user data from the request body
        $name = $requestBody['name'] ?? $company->getName();
        $address = $requestBody['address'] ?? $company->getAddress();
        $city = $requestBody['city'] ?? $company->getCity();
        $country = $requestBody['country'] ?? $company->getCountry();
        $zipCode = $requestBody['zipCode'] ?? $company->getZipCode();
        $phone = $requestBody['phone'] ?? $company->getPhone();
        $slogan = $requestBody['slogan'] ?? $company->getSlogan();
        $logoPath = $requestBody['logoPath'] ?? $company->getLogoPath();
        $language = $requestBody['language'] ?? $company->getLanguage();
        $licenseId = $requestBody['license'] ?? $company->getLicense()->getName();

        // get the license from the database by its name
        try {
            $licenseId = $this->dao->getOneBy(License::class, ['id' => $licenseId]);
            // if the license is not found, return an error
            if (!$licenseId) {
                $this->request->handleErrorAndQuit(404, new Exception('License not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        // add the validity period to the current date
        try {
            $licenseExpirationDate->add(new DateInterval('P' . $licenseId->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // check if the license is expired, if it is, return set $isEnable to false
        $isEnabled = true;

        if ($licenseExpirationDate < new DateTime()) {
            $isEnabled = false;
        }

        // update the company data checking if the data has been changed
        $company->setName($name);
        $company->setAddress($address);
        $company->setCity($city);
        $company->setCountry($country);
        $company->setZipCode($zipCode);
        $company->setPhone($phone);
        $company->setSlogan($slogan);
        $company->setLogoPath($logoPath);
        $company->setLicense($licenseId);
        $company->setLanguage($language);
        $company->setIsEnabled($isEnabled);

        // update the company
        try {
            $this->dao->update($company);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Company updated successfully');

    }

    /**
     * @OA\Delete(
     *     path="/company/{id}",
     *     tags={"Company"},
     *     summary="Delete a company by id",
     *     description="Deletes a company by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of the company to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteCompany(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneBy(Company::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // remove the company
        try {
            $this->dao->delete($company);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Company deleted successfully');

    }
}

