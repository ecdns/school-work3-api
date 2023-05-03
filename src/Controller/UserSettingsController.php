<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\UserSettings;
use Exception;
use Service\Request;

class UserSettingsController extends AbstractController
{

    private EntityManager $entityManager;
    private const REQUIRED_FIELDS = ['theme', 'language', 'user-id'];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->entityManager->find('Entity\User', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user is not found
        if (!$user) {
            Request::handleErrorAndQuit(404, new Exception('User not found'));
        }

        // create a new user settings
        $userSettings = new UserSettings($theme, $language, $user);

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // update the user
        $user->setUserSettings($userSettings);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(400, new Exception('User settings already exist'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'User settings created successfully');

    }

    public function getUserSettingsById(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // prepare the user settings data
        $userSettings = $userSettings->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'User settings found', $userSettings);
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
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'] ?? null;
        $language = $requestBody['language'] ?? null;

        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // update the user settings
        $userSettings->setTheme($theme ?? $userSettings->getTheme());
        $userSettings->setLanguage($language ?? $userSettings->getLanguage());

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
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
        Request::handleSuccessAndQuit(200, 'User settings updated successfully');
    }

    public function deleteUserSettings(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(404, new Exception('User settings not found'));
        }

        // remove the user settings
        try {
            $this->entityManager->remove($userSettings);
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
        Request::handleSuccessAndQuit(200, 'User settings deleted successfully');
    }
}