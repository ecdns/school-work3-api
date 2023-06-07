<?php

declare(strict_types=1);

namespace Controller;

use Entity\UserSettings;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="UserSettingsRequest",
 *     required={"theme", "language", "user-id"},
 *     @OA\Property(property="theme", type="string", example="dark"),
 *     @OA\Property(property="language", type="string", example="en"),
 *     @OA\Property(property="user-id", type="integer", example="1")
 * )
 *
 * @OA\Schema (
 *     schema="UserSettingsResponse",
 *     required={"id", "theme", "language", "user-id"},
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="theme", type="string", example="dark"),
 *     @OA\Property(property="language", type="string", example="en"),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserResponse"),
 *     @OA\Property(property="created-at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated-at", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class UserSettingsController extends AbstractController
{

    private const REQUIRED_FIELDS = ['theme', 'language', 'user-id'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/user-settings",
     *     tags={"User Settings"},
     *     summary="Add user settings",
     *     description="Add user settings",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User settings object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/UserSettingsRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User settings created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addUserSettings(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "theme": "dark",
        //     "language": "en",
        //     "user-id": "user@user"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->dao->getOneBy('Entity\User', ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user is not found
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // create a new user settings
        $userSettings = new UserSettings($theme, $language, $user);

        // update the user
        $user->setUserSettings($userSettings);

        // add the user settings to the database (this will also update the user)
        try {
            $this->dao->add($userSettings);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(400, new Exception('User settings already exist'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'User settings created successfully');

    }


    /**
     * @OA\Get(
     *     path="/user-settings/{id}",
     *     tags={"User Settings"},
     *     summary="Get user settings by id",
     *     description="Get user settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user settings to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User settings found",
     *         @OA\JsonContent(ref="#/components/schemas/UserSettingsResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User settings not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getUserSettingsById(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->dao->getOneBy('Entity\UserSettings', ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            $this->request->handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // prepare the user settings data
        $userSettings = $userSettings->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User settings found', $userSettings);
    }


    /**
     * @OA\Put(
     *     path="/user-settings/{id}",
     *     tags={"User Settings"},
     *     summary="Update user settings by id",
     *     description="Update user settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user settings to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User settings object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/UserSettingsRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User settings updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data or user settings already exist"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User settings not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateUserSettings(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "theme": "dark",
        //     "language": "en"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings
        try {
            $userSettings = $this->dao->getOneBy('Entity\UserSettings', ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            $this->request->handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'] ?? $userSettings->getTheme();
        $language = $requestBody['language'] ?? $userSettings->getLanguage();

        // update the user settings
        $userSettings->setTheme($theme);
        $userSettings->setLanguage($language);

        // flush the entity manager
        try {
            $this->dao->update($userSettings);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(400, new Exception('User settings already exist'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User settings updated successfully');
    }


    /**
     * @OA\Delete(
     *     path="/user-settings/{id}",
     *     tags={"User Settings"},
     *     summary="Delete user settings by id",
     *     description="Delete user settings by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user settings to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User settings deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User settings not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteUserSettings(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->dao->getOneBy('Entity\UserSettings', ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            $this->request->handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // remove the user settings
        try {
            $this->dao->delete($userSettings);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User settings deleted successfully');
    }
}