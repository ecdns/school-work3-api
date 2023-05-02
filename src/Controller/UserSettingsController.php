<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\UserSettings;
use Exception;
use Service\HttpHelper;
use Service\LogManager;
use Service\RequestManager;

class UserSettingsController implements ControllerInterface
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data, bool $isPostRequest = true): bool
    {
        if ($isPostRequest) {
            if (!isset($data['theme']) || !isset($data['language']) || !isset($data['user-id'])) {
                return false;
            } else {
                return true;
            }
        } else {
            if (isset($data['theme']) || isset($data['language']) || isset($data['user-id'])) {
                return true;
            } else {
                return false;
            }
        }
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
        if (!$this->validateData($requestBody)) {
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->entityManager->find('Entity\User', $id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the user is not found
        if (!$user) {
            RequestManager::handleErrorAndQuit(new Exception('User not found'), 404);
        }

        // create a new user settings
        $userSettings = new UserSettings($theme, $language, $user);

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // update the user
        $user->setUserSettings($userSettings);

        // persist the user
        try {
            $this->entityManager->persist($user);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                RequestManager::handleErrorAndQuit(new Exception('User settings already exist'), 409);
            }
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // send the response
        HttpHelper::sendStatusResponse(200, 'User settings added successfully');

        // log the event
        $logMessage = LogManager::getContext() . ' - User settings added successfully';
        LogManager::addInfoLog($logMessage);

    }

    public function getUserSettingsById(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            RequestManager::handleErrorAndQuit(new Exception('User settings not found'), 404);
        }

        // prepare the user settings data
        $userSettings = $userSettings->toArray();

        // send the response
        HttpHelper::sendDataResponse(200, $userSettings);

        // log the event
        $logMessage = LogManager::getContext() . ' - User settings retrieved successfully';
        LogManager::addInfoLog($logMessage);
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
        if (!$this->validateData($requestBody, false)) {
            RequestManager::handleErrorAndQuit(new Exception('Invalid data'), 400);
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'] ?? false;
        $language = $requestBody['language'] ?? false;

        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            RequestManager::handleErrorAndQuit(new Exception('User settings not found'), 404);
        }

        // update the user settings
        $userSettings->setTheme($theme ?? $userSettings->getTheme());
        $userSettings->setLanguage($language ?? $userSettings->getLanguage());

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // send the response
        HttpHelper::sendStatusResponse(200, 'User settings updated successfully');

        // log the event
        $logMessage = LogManager::getContext() . ' - User settings updated successfully';
        LogManager::addInfoLog($logMessage);
    }

    public function deleteUserSettings(int $id): void
    {
        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // if the user settings are not found
        if (!$userSettings) {
            RequestManager::handleErrorAndQuit(new Exception('User settings not found'), 404);
        }

        // remove the user settings
        try {
            $this->entityManager->remove($userSettings);
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            RequestManager::handleErrorAndQuit($e, 500);
        }

        // send the response
        HttpHelper::sendStatusResponse(200, 'User settings deleted successfully');

        // log the event
        $logMessage = LogManager::getContext() . ' - User settings deleted successfully';
        LogManager::addInfoLog($logMessage);
    }
}