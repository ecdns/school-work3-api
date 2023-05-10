<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\UserSettings;
use Exception;
use Service\DAO;
use Service\Request;

class UserSettingsController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['theme', 'language', 'user-id'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->dao->getOneEntityBy('Entity\User', ['id' => $id]);
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
            $this->dao->addEntity($userSettings);
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

    public function getUserSettingsById(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->dao->getOneEntityBy('Entity\UserSettings', ['id' => $id]);
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
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings
        try {
            $userSettings = $this->dao->getOneEntityBy('Entity\UserSettings', ['id' => $id]);
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
            $this->dao->updateEntity($userSettings);
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

    public function deleteUserSettings(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->dao->getOneEntityBy('Entity\UserSettings', ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            $this->request->handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // remove the user settings
        try {
            $this->dao->deleteEntity($userSettings);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User settings deleted successfully');
    }
}