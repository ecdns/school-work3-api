<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Entity\Company;
use Entity\CompanySettings;
use Service\HttpHelper;
use Service\LogManager;

class CompanySettingsController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addCompanySettings(): void
    {

        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "primaryColor": "#000000",
        //     "secondaryColor": "#000000",
        //     "tertiaryColor": "#000000",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'];
        $secondaryColor = $requestBody['secondaryColor'];
        $tertiaryColor = $requestBody['tertiaryColor'];
        $company = $requestBody['company'];

        // get the company from the database by its name
        try {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $company]);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'Company not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // create a new companySettings object
        $companySettings = new CompanySettings($primaryColor, $secondaryColor, $tertiaryColor, $company);

        // update the company settings field in the company object
        $company->setCompanySettings($companySettings);

        // persist
        try {
            $this->entityManager->persist($companySettings);
            $this->entityManager->persist($company);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(500, $error, true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // set the response
        HttpHelper::setResponse(201, 'Company settings created', true);

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings created';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanySettingsById(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'Company settings not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $companySettings->toArray();

        HttpHelper::setResponse(200, 'Company settings found', false);
        HttpHelper::setResponseData($response);

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings found';
        LogManager::addInfoLog($logMessage);
    }

    public function updateCompanySettings(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "primaryColor": "#000000",
        //     "secondaryColor": "#000000",
        //     "tertiaryColor": "#000000",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'];
        $secondaryColor = $requestBody['secondaryColor'];
        $tertiaryColor = $requestBody['tertiaryColor'];
        $company = $requestBody['company'];

        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'Company settings not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the company settings
        $companySettings->setPrimaryColor($primaryColor);
        $companySettings->setSecondaryColor($secondaryColor);
        $companySettings->setTertiaryColor($tertiaryColor);

        // persist
        try {
            $this->entityManager->persist($companySettings);
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
        HttpHelper::setResponse(200, 'Company settings updated', true);

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings updated';
        LogManager::addInfoLog($logMessage);
    }

    public function deleteCompanySettings(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (ORMException $e) {
            $error = $e->getMessage();
            HttpHelper::setResponse(404, 'Company settings not found', true);
            $logMessage = LogManager::getContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the company settings
        try {
            $this->entityManager->remove($companySettings);
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
        HttpHelper::setResponse(200, 'Company settings deleted', true);

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings deleted';
        LogManager::addInfoLog($logMessage);
    }
}