<?php

declare(strict_types=1);

namespace Service;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

class RequestManager
{
    #[NoReturn] public static function handleErrorAndQuit(Throwable $e, int $httpCode): void
    {
        $message = $e->getMessage();
        $context = LogManager::getFullContext();
        $error = $message . ' - ' . $context;
        LogManager::addErrorLog($error);
        HttpHelper::sendStatusResponse($httpCode, $message);
        exit(1);
    }

    #[NoReturn] public static function handleSuccessAndQuit(int $httpCode, string $status, $data = null): void
    {

        if ($data !== null) {
            HttpHelper::sendDataResponse($httpCode, $data);
        } else {
            HttpHelper::sendStatusResponse($httpCode, $status);
        }
        LogManager::addInfoLog($status);
        exit(0);
    }
}