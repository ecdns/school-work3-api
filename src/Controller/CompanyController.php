<?php

namespace Controller;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
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
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the license expiration date knowing it will be in years
        $licenseExpirationDate = new DateTime();

        try {
            $licenseExpirationDate->add(new DateInterval('P' . $license->getValidityPeriod() . 'Y'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
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
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // flush the entity manager

        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }



        // set the response
        HttpHelper::setResponse(200, 'Company added successfully', true);

        // add a log
        $logMessage = LogManager::getContext() . ' - Company added successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanyById(int $id): void
    {
        // get the company from the database by its id
        try {
            $company = $this->entityManager->getRepository(Company::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found, return an error
        if (!$company) {
            HttpHelper::setResponse(404, 'Company not found', true);
            $logMessage = LogManager::getContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // construct the response with the company data
        $response = $company->toArray();

        // set the response
        HttpHelper::setResponse(200, 'Company found', false);
        HttpHelper::setResponseData($response);

    }

}