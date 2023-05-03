<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\CompanySettings;
use Exception;
use Service\Request;

class CompanySettingsController extends AbstractController
{
    private EntityManager $entityManager;
    private const REQUIRED_FIELDS = ['primaryColor', 'secondaryColor', 'tertiaryColor', 'company'];

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

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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
            Request::handleErrorAndQuit(500, $e);
        }

        // if the company is not found`
        if (!$company) {
            Request::handleErrorAndQuit(404, new Exception('Company not found'));
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
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Company settings already exist'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'Company settings created');

    }

    public function getCompanySettingsById(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            Request::handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // set the response
        $response = $companySettings->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'Company settings found', $response);
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
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'] ?? null;
        $secondaryColor = $requestBody['secondaryColor'] ?? null;
        $tertiaryColor = $requestBody['tertiaryColor'] ?? null;

        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            Request::handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // update the company settings
        $companySettings->setPrimaryColor($primaryColor ?? $companySettings->getPrimaryColor());
        $companySettings->setSecondaryColor($secondaryColor ?? $companySettings->getSecondaryColor());
        $companySettings->setTertiaryColor($tertiaryColor ?? $companySettings->getTertiaryColor());

        // persist
        try {
            $this->entityManager->persist($companySettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Company settings updated');
    }

    public function deleteCompanySettings(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->entityManager->getRepository(CompanySettings::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            Request::handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // remove the company settings
        try {
            $this->entityManager->remove($companySettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Company settings deleted');
    }
}