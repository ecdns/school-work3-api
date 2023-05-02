<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\CompanySettings;
use Exception;
use Service\RequestManager;

class CompanySettingsController implements ControllerInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data, bool $isPostRequest = true): bool
    {
        if ($isPostRequest) {
            if (!isset($data['primaryColor']) || !isset($data['secondaryColor']) || !isset($data['tertiaryColor']) || !isset($data['company'])) {
                return false;
            } else {
                return true;
            }
        } else {
            if (isset($data['primaryColor']) || isset($data['secondaryColor']) || isset($data['tertiaryColor']) || isset($data['company'])) {
                return true;
            } else {
                return false;
            }
        }
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

        // validate the data
        if (!$this->validateData($requestBody)) {
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'];
        $secondaryColor = $requestBody['secondaryColor'];
        $tertiaryColor = $requestBody['tertiaryColor'];
        $company = $requestBody['company'];

        // get the company from the database by its name
        try {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $company]);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the company is not found`
        if (!$company) {
            RequestManager::handleErrorAndQuit(new Exception('Company not found'), 404);
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
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                RequestManager::handleErrorAndQuit(new Exception('Company settings already exist'), 409);
            }
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(201, 'Company settings created');

    }

    public function getCompanySettingsById(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the company settings are not found
        if (!$companySettings) {
            RequestManager::handleErrorAndQuit(new Exception('Company settings not found'), 404);
        }

        // set the response
        $response = $companySettings->toArray();

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'Company settings found', $response);
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

        // validate the data
        if (!$this->validateData($requestBody, false)) {
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'] ?? false;
        $secondaryColor = $requestBody['secondaryColor'] ?? false;
        $tertiaryColor = $requestBody['tertiaryColor'] ?? false;

        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the company settings are not found
        if (!$companySettings) {
            RequestManager::handleErrorAndQuit(new Exception('Company settings not found'), 404);
        }

        // update the company settings
        $companySettings->setPrimaryColor($primaryColor ?? $companySettings->getPrimaryColor());
        $companySettings->setSecondaryColor($secondaryColor ?? $companySettings->getSecondaryColor());
        $companySettings->setTertiaryColor($tertiaryColor ?? $companySettings->getTertiaryColor());

        // persist
        try {
            $this->entityManager->persist($companySettings);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'Company settings updated');
    }

    public function deleteCompanySettings(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the company settings are not found
        if (!$companySettings) {
            RequestManager::handleErrorAndQuit(new Exception('Company settings not found'), 404);
        }

        // remove the company settings
        try {
            $this->entityManager->remove($companySettings);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // handle the response
        RequestManager::handleSuccessAndQuit(200, 'Company settings deleted');
    }
}