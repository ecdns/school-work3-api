<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\UserSettings;
use Exception;
use Service\Request;

class UserSettingsController implements ControllerInterface
{

    private EntityManager $entityManager;
    private const DATA = ['theme', 'language', 'user-id'];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validatePostData(mixed $data): bool
    {
        foreach (self::DATA as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }
        return true;
    }

    public function validatePutData(mixed $data): bool
    {
        foreach (self::DATA as $key) {
            if (isset($data[$key])) {
                return true;
            }
        }
        return false;
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
        if (!$this->validatePostData($requestBody)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->entityManager->find('Entity\User', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user is not found
        if (!$user) {
            Request::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // create a new user settings
        $userSettings = new UserSettings($theme, $language, $user);

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // update the user
        $user->setUserSettings($userSettings);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(new Exception('User settings already exist'), 409);
            }
            Request::handleErrorAndQuit($e, 500);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(new Exception('User settings not found'), 404);
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
        if (!$this->validatePutData($requestBody)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'] ?? null;
        $language = $requestBody['language'] ?? null;

        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(new Exception('User settings not found'), 404);
        }

        // update the user settings
        $userSettings->setTheme($theme ?? $userSettings->getTheme());
        $userSettings->setLanguage($language ?? $userSettings->getLanguage());

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            Request::handleErrorAndQuit(new Exception('User settings not found'), 404);
        }

        // remove the user settings
        try {
            $this->entityManager->remove($userSettings);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'User settings deleted successfully');
    }
}