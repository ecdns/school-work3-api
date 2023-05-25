<?php

declare(strict_types=1);

namespace Controller;

use Entity\Company;
use Entity\CompanySettings;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema(
*    schema="CompanySettingsRequest",
 *   required={"primaryColor", "secondaryColor", "tertiaryColor", "company"},
 *   @OA\Property(property="primaryColor", type="string", example="#000000"),
 *   @OA\Property(property="secondaryColor", type="string", example="#000000"),
 *   @OA\Property(property="tertiaryColor", type="string", example="#000000"),
 *   @OA\Property(property="company", type="id", example=1)
 * )
 * @OA\Schema(
 *   schema="CompanySettingsResponse",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="primaryColor", type="string", example="#000000"),
 *   @OA\Property(property="secondaryColor", type="string", example="#000000"),
 *   @OA\Property(property="tertiaryColor", type="string", example="#000000"),
 *   @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/company-settings",
     *     tags={"Company Settings"},
     *     summary="Add company settings",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CompanySettingsRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Company settings created"
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
     *         response="409",
     *         description="Company settings already exist"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Company settings could not be created"
     *     )
     * )
     */
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
        $companyId = $requestBody['company'];

        // get the company from the database by its name
        try {
            $companyId = $this->dao->getOneEntityBy(Company::class, ['id' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the company is not found`
        if (!$companyId) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // create a new companySettings object
        $companySettings = new CompanySettings($primaryColor, $secondaryColor, $tertiaryColor, $companyId);

        // update the company settings field in the company object
        $companyId->setCompanySettings($companySettings);

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

    /**
     * @OA\Get(
     *     path="/company-settings/{id}",
     *     tags={"Company Settings"},
     *     summary="Get company settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company settings",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Company settings found",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySettingsResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company settings not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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


    /**
     * @OA\Put(
     *     path="/company-settings/{id}",
     *     tags={"Company Settings"},
     *     summary="Update company settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company settings",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Company settings object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySettingsRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Company settings updated"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company settings not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Company settings already exist"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Company settings could not be created"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/company-settings/{id}",
     *     tags={"Company Settings"},
     *     summary="Delete company settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company settings",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Company settings deleted"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Company settings not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
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