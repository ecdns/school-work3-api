<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\UserSettings;
use Exception;
use Service\HttpHelper;
use Service\LogManager;

class UserSettingsController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data): bool
    {
        // check if some data is missing, if so return false, else return true
        if (!isset($data['theme']) || !isset($data['language']) || !isset($data['user-id'])) {
            return false;
        } else {
            return true;
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
            HttpHelper::sendRequestState(400, 'Invalid data');
            $logMessage = LogManager::getFullContext() . ' - Invalid data';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // get the user settings data from the request body
        $theme = $requestBody['theme'];
        $language = $requestBody['language'];
        $id = $requestBody['user-id'];

        // get the user by its id
        try {
            $user = $this->entityManager->find('Entity\User', $id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user is not found
        if (!$user) {
            HttpHelper::sendRequestState(404, 'User not found');
            $logMessage = LogManager::getFullContext() . ' - User not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // create a new user settings
        $userSettings = new UserSettings($theme, $language, $user);

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the user
        $user->setUserSettings($userSettings);

        // persist the user
        try {
            $this->entityManager->persist($user);
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

        // send the response
        HttpHelper::sendRequestState(200, 'User settings added successfully');

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
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user settings are not found
        if (!$userSettings) {
            HttpHelper::sendRequestState(404, 'User settings not found');
            $logMessage = LogManager::getFullContext() . ' - User settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // prepare the user settings data
        $userSettings = $userSettings->toArray();

        // send the response
        HttpHelper::sendRequestData(200, $userSettings);

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

        // get the user settings data from the request body
        $theme = $requestBody['theme'] ?? false;
        $language = $requestBody['language'] ?? false;

        // get the user settings
        try {
            $userSettings = $this->entityManager->find('Entity\UserSettings', $id);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user settings are not found
        if (!$userSettings) {
            HttpHelper::sendRequestState(404, 'User settings not found');
            $logMessage = LogManager::getFullContext() . ' - User settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // update the user settings
        if ($theme) {
            $userSettings->setTheme($theme);
        }

        if ($language) {
            $userSettings->setLanguage($language);
        }

        // if no data was provided
        if (!$theme && !$language) {
            HttpHelper::sendRequestState(400, 'No valid data provided');
            $logMessage = LogManager::getFullContext() . ' - No valid data provided';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // persist the user settings
        try {
            $this->entityManager->persist($userSettings);
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

        // send the response
        HttpHelper::sendRequestState(200, 'User settings updated successfully');

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
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // if the user settings are not found
        if (!$userSettings) {
            HttpHelper::sendRequestState(404, 'User settings not found');
            $logMessage = LogManager::getFullContext() . ' - User settings not found';
            LogManager::addErrorLog($logMessage);
            exit(1);
        }

        // remove the user settings
        try {
            $this->entityManager->remove($userSettings);
        } catch (Exception $e) {
            $error = $e->getMessage();
            HttpHelper::sendRequestState(500, $error);
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            $logMessage = LogManager::getFullContext() . ' - ' . $error;
            LogManager::addErrorLog($logMessage);
        }

        // send the response
        HttpHelper::sendRequestState(200, 'User settings deleted successfully');

        // log the event
        $logMessage = LogManager::getContext() . ' - User settings deleted successfully';
        LogManager::addInfoLog($logMessage);
    }
}