<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\CompanySettings;
use Exception;
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
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company is not found`
        if (!$company) {
            HttpHelper::sendRequestState(404, 'Company not found');
            $logMessage = LogManager::getFullContext() . ' - Company not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // create a new companySettings object
        $companySettings = new CompanySettings($primaryColor, $secondaryColor, $tertiaryColor, $company);

        // update the company settings field in the company object
        $company->setCompanySettings($companySettings);

        // persist
        try {
            $this->entityManager->persist($companySettings);
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

        // set the response
        HttpHelper::sendRequestState(201, 'Company settings created');

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings created';
        LogManager::addInfoLog($logMessage);

    }

    public function getCompanySettingsById(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company settings are not found
        if (!$companySettings) {
            HttpHelper::sendRequestState(404, 'Company settings not found');
            $logMessage = LogManager::getFullContext() . ' - Company settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // set the response
        $response = $companySettings->toArray();

        HttpHelper::sendRequestData(200, $response);

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
        $primaryColor = $requestBody['primaryColor'] ?? false;
        $secondaryColor = $requestBody['secondaryColor'] ?? false;
        $tertiaryColor = $requestBody['tertiaryColor'] ?? false;

        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company settings are not found
        if (!$companySettings) {
            HttpHelper::sendRequestState(404, 'Company settings not found');
            $logMessage = LogManager::getFullContext() . ' - Company settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the company settings
        if ($primaryColor) {
            $companySettings->setPrimaryColor($primaryColor);
        }

        if ($secondaryColor) {
            $companySettings->setSecondaryColor($secondaryColor);
        }

        if ($tertiaryColor) {
            $companySettings->setTertiaryColor($tertiaryColor);
        }

        // if no data was provided
        if (!$primaryColor && !$secondaryColor && !$tertiaryColor) {
            HttpHelper::sendRequestState(400, 'No valid data provided');
            $logMessage = LogManager::getFullContext() . ' - No valid data provided';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // persist
        try {
            $this->entityManager->persist($companySettings);
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
        HttpHelper::sendRequestState(200, 'Company settings updated');

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings updated';
        LogManager::addInfoLog($logMessage);
    }

    public function deleteCompanySettings(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(404, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the company settings are not found
        if (!$companySettings) {
            HttpHelper::sendRequestState(404, 'Company settings not found');
            $logMessage = LogManager::getFullContext() . ' - Company settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the company settings
        try {
            $this->entityManager->remove($companySettings);
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
        HttpHelper::sendRequestState(200, 'Company settings deleted');

        // log the event
        $logMessage = LogManager::getContext() . ' - Company settings deleted';
        LogManager::addInfoLog($logMessage);
    }
}