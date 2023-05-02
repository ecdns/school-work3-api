<?php

namespace Controller;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\License;
use Exception;
use Service\HttpHelper;
use Service\LogManager;

class CompanyController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the license is not found
        if (!$license) {
            HttpHelper::sendRequestState(404, 'License not found');
            $logMessage = LogManager::getFullContext() . ' - License not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            // if the error mentions a constraint violation, it means the license already exists
            if (str_contains($error, 'constraint violation')) {
                HttpHelper::sendRequestState(409, 'License already exists');
                $logMessage = LogManager::getFullContext() . ' - License already exists';
                LogManager::addErrorLog($logMessage);
                exit(1);
            }
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
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
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'Company added successfully');

        // add a log
        $logMessage = LogManager::getFullContext() . ' - Company added successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanies(): void
    {
        // get the companies from the database
        try {
            $companies = $this->entityManager->getRepository(Company::class)->findAll();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // construct the response with the companies data
        $response = [];
        foreach ($companies as $company) {
            $response[] = $company->toArray();
        }

        // set the response
        HttpHelper::sendRequestData(200, $response);

        // add a log
        $logMessage = LogManager::getContext() . ' - Companies found';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanyById(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found, return an error
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response

        HttpHelper::sendRequestData(200, $response);

        // add a log
        $logMessage = LogManager::getContext() . ' - Company found';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanyByName(string $name): void
    {
        // get the company from the database by its name
        try {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $name]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found, return an error
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // construct the response with the company data
        $response = $company->toFullArrayWithUsers();

        // set the response
        HttpHelper::sendRequestData(200, $response);

        // add a log
        $logMessage = LogManager::getContext() . ' - Company found';
        LogManager::addInfoLog($logMessage);

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

        // get the user data from the request body
        $name = $requestBody['name'] ?? false;
        $address = $requestBody['address'] ?? false;
        $city = $requestBody['city'] ?? false;
        $country = $requestBody['country'] ?? false;
        $zipCode = $requestBody['zipCode'] ?? false;
        $phone = $requestBody['phone'] ?? false;
        $slogan = $requestBody['slogan'] ?? false;
        $logoPath = $requestBody['logoPath'] ?? false;
        $licenseName = $requestBody['license'] ?? false;
        $language = $requestBody['language'] ?? false;

        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found, return an error
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the license from the database by its name
        try {
            $license = $this->entityManager->getRepository(License::class)->findOneBy(['name' => $company->getLicense()->getName()]);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the license is not found, return an error
        if (!$license) {
            HttpHelper::sendRequestState(404, 'License not found');
            $logMessage = LogManager::getFullContext() . ' - License not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // check if the license is expired, if it is, return set $isEnable to false
        $isEnabled = true;

        if ($licenseExpirationDate < new DateTime()) {
            $isEnabled = false;
        }

        // update the company
        if ($name) {
            $company->setName($name);
        }

        if ($address) {
            $company->setAddress($address);
        }

        if ($city) {
            $company->setCity($city);
        }

        if ($country) {
            $company->setCountry($country);
        }

        if ($zipCode) {
            $company->setZipCode($zipCode);
        }

        if ($phone) {
            $company->setPhone($phone);
        }

        if ($slogan) {
            $company->setSlogan($slogan);
        }

        if ($logoPath) {
            $company->setLogoPath($logoPath);
        }

        $company->setLicense($license);

        if ($language) {
            $company->setLanguage($language);
        }

        $company->setIsEnabled($isEnabled);

        // if no data has been changed, return an error
        if (!$name && !$address && !$city && !$country && !$zipCode && !$phone && !$slogan && !$logoPath && !$licenseName && !$language) {
            HttpHelper::sendRequestState(400, 'No valid data provided');
            $logMessage = LogManager::getFullContext() . ' - No valid data provided';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // persist the company
        try {
            $this->entityManager->persist($company);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'Company updated successfully');

        // add a log
        $logMessage = LogManager::getContext() . ' - Company updated successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function deleteCompany(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found, return an error
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the company
        try {
            $this->entityManager->remove($company);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        HttpHelper::sendRequestState(200, 'Company deleted successfully');

        // add a log
        $logMessage = LogManager::getContext() . ' - Company deleted successfully';
        LogManager::addInfoLog($logMessage);

    }
}

