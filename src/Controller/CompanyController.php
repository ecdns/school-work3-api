<?php

declare(strict_types=1);

namespace Controller;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\License;
use Exception;
use Service\DAO;
use Service\Request;

class CompanyController extends AbstractController
{
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'address', 'city', 'country', 'zipCode', 'phone', 'slogan', 'logoPath', 'license', 'language'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
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
        $licenseName = $requestBody['license'];
        $language = $requestBody['language'];

        // get the license from the database by its name
        try {
            $license = $this->dao->getOneEntityBy(License::class, ['name' => $licenseName]);
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
            $this->dao->addEntity($company);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(201, 'Company created');

    }

    public function getCompanies(): void
    {
        // get the companies from the database
        try {
            $companies = $this->dao->getAllEntities(Company::class);
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

    public function getCompanyById(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['id' => $id]);
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

    public function getCompanyByName(string $name): void
    {
        // get the company from the database by its name
        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['name' => $name]);
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
        if ($this->validatePutData($requestBody, self::REQUIRED_FIELDS) === false) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the company from the database by its id
        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['id' => $id]);
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
        $license = $requestBody['license'] ?? $company->getLicense()->getName();

        // get the license from the database by its name
        try {
            $license = $this->dao->getOneEntityBy(License::class, ['name' => $license]);
            // if the license is not found, return an error
            if (!$license) {
                $this->request->handleErrorAndQuit(404, new Exception('License not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        // add the validity period to the current date
        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
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
        $company->setLicense($license);
        $company->setLanguage($language);
        $company->setIsEnabled($isEnabled);

        // update the company
        try {
            $this->dao->updateEntity($company);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $this->request->handleSuccessAndQuit(200, 'Company updated successfully');

    }

    public function deleteCompany(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found, return an error
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // remove the company
        try {
            $this->dao->deleteEntity($company);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Company deleted successfully');

    }
}

