<?php

namespace Controller;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\License;
use Exception;
use Service\Request;

class CompanyController implements ControllerInterface
{
    private EntityManager $entityManager;
    private const DATA = ['name', 'address', 'city', 'country', 'zipCode', 'phone', 'slogan', 'logoPath', 'license', 'language'];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validatePostData(mixed $data): bool
    {
        foreach (self::DATA as $value) {
            if (!isset($data[$value])) {
                return false;
            }
        }
        return true;
    }

    public function validatePutData(mixed $data): bool
    {
        foreach (self::DATA as $value) {
            if (isset($data[$value])) {
                return true;
            }
        }
        return false;
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
        if (!$this->validatePostData($requestBody)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
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
            $license = $this->entityManager->getRepository(License::class)->findOneBy(['name' => $licenseName]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the license is not found
        if (!$license) {
            Request::handleErrorAndQuit(new Exception('License not found'), 404);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            // if the error mentions a constraint violation, it means the license already exists
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(new Exception('License already exists'), 409);
            }
            Request::handleErrorAndQuit($e, 500);
        }

        // check if the license is expired, if it is, return set $isEnable to false
        $isEnabled = true;
        if ($licenseExpirationDate < new DateTime()) {
            $isEnabled = false;
        }

        // create a new Company
        $company = new Company($name, $address, $city, $country, $zipCode, $phone, $slogan, $logoPath, $license, $licenseExpirationDate, $language, $isEnabled);

        // persist the company
        try {
            $this->entityManager->persist($company);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // set the response
        Request::handleSuccessAndQuit(201, 'Company created');

    }

    public function getCompanies(): void
    {
        // get the companies from the database
        try {
            $companies = $this->entityManager->getRepository(Company::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // construct the response with the companies data
        $response = [];
        foreach ($companies as $company) {
            $response[] = $company->toArray();
        }

        // set the response
        Request::handleSuccessAndQuit(200, 'Companies found', $response);

    }

    public function getCompanyById(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the company is not found, return an error
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response
        Request::handleSuccessAndQuit(200, 'Company found', $response);

    }

    public function getCompanyByName(string $name): void
    {
        // get the company from the database by its name
        try {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $name]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the company is not found, return an error
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response
        Request::handleSuccessAndQuit(200, 'Company found', $response);

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
        if ($this->validatePutData($requestBody) === false) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user data from the request body
        $name = $requestBody['name'] ?? null;
        $address = $requestBody['address'] ?? null;
        $city = $requestBody['city'] ?? null;
        $country = $requestBody['country'] ?? null;
        $zipCode = $requestBody['zipCode'] ?? null;
        $phone = $requestBody['phone'] ?? null;
        $slogan = $requestBody['slogan'] ?? null;
        $logoPath = $requestBody['logoPath'] ?? null;
        $language = $requestBody['language'] ?? null;

        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the company is not found, return an error
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // get the license from the database by its name
        try {
            $license = $this->entityManager->getRepository(License::class)->findOneBy(['name' => $company->getLicense()->getName()]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the license is not found, return an error
        if (!$license) {
            Request::handleErrorAndQuit(new Exception('License not found'), 404);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // check if the license is expired, if it is, return set $isEnable to false
        $isEnabled = true;

        if ($licenseExpirationDate < new DateTime()) {
            $isEnabled = false;
        }

        // update the company data checking if the data has been changed
        $company->setName($name ?? $company->getName());
        $company->setAddress($address ?? $company->getAddress());
        $company->setCity($city ?? $company->getCity());
        $company->setCountry($country ?? $company->getCountry());
        $company->setZipCode($zipCode ?? $company->getZipCode());
        $company->setPhone($phone ?? $company->getPhone());
        $company->setSlogan($slogan ?? $company->getSlogan());
        $company->setLogoPath($logoPath ?? $company->getLogoPath());
        $company->setLicense($license);
        $company->setLanguage($language ?? $company->getLanguage());
        $company->setIsEnabled($isEnabled);

        // persist the company
        try {
            $this->entityManager->persist($company);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // set the response
        Request::handleSuccessAndQuit(200, 'Company updated successfully');

    }

    public function deleteCompany(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the company is not found, return an error
        if (!$company) {
            Request::handleErrorAndQuit(new Exception('Company not found'), 404);
        }

        // remove the company
        try {
            $this->entityManager->remove($company);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Company deleted successfully');

    }
}

