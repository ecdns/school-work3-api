<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\CompanySettings;
use Exception;
use Service\DAO;
use Service\Request;

class CompanySettingsController extends AbstractController
{
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['primaryColor', 'secondaryColor', 'tertiaryColor', 'company'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
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
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'];
        $secondaryColor = $requestBody['secondaryColor'];
        $tertiaryColor = $requestBody['tertiaryColor'];
        $company = $requestBody['company'];

        // get the company from the database by its name
        try {
            $company = $this->dao->getOneEntityBy(Company::class, ['name' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found`
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // create a new companySettings object
        $companySettings = new CompanySettings($primaryColor, $secondaryColor, $tertiaryColor, $company);

        // update the company settings field in the company object
        $company->setCompanySettings($companySettings);

        // persist (save) the company settings in the database (this will also save the company settings in the company table)
        try {
            $this->dao->addEntity($companySettings);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company settings already exist'));
            }
            $this->request->handleErrorAndQuit(500, new Exception('Company settings could not be created : ' . $e->getMessage()));
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Company settings created');

    }

    public function getCompanySettingsById(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->dao->getOneEntityBy(CompanySettings::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            $this->request->handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // set the response
        $response = $companySettings->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Company settings found', $response);
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
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the company settings from the database by its id
        try {
            $companySettings = $this->dao->getOneEntityBy(CompanySettings::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            $this->request->handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // get the user data from the request body
        $primaryColor = $requestBody['primaryColor'] ?? $companySettings->getPrimaryColor();
        $secondaryColor = $requestBody['secondaryColor'] ?? $companySettings->getSecondaryColor();
        $tertiaryColor = $requestBody['tertiaryColor'] ?? $companySettings->getTertiaryColor();

        // update the company settings
        $companySettings->setPrimaryColor($primaryColor);
        $companySettings->setSecondaryColor($secondaryColor);
        $companySettings->setTertiaryColor($tertiaryColor);

        // persist
        try {
            $this->dao->updateEntity($companySettings);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Company settings already exist'));
            }
            $this->request->handleErrorAndQuit(500, new Exception('Company settings could not be created : ' . $e->getMessage()));
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Company settings updated');
    }

    public function deleteCompanySettings(int $id): void
    {
        // get the company settings from the database by its id
        try {
            $companySettings = $this->dao->getOneEntityBy(CompanySettings::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company settings are not found
        if (!$companySettings) {
            $this->request->handleErrorAndQuit(404, new Exception('Company settings not found'));
        }

        // remove the company settings
        try {
            $this->dao->deleteEntity($companySettings);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Company settings deleted');
    }
}