<?php

declare(strict_types=1);

namespace Service;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

class Request
{
    #[NoReturn] public static function handleErrorAndQuit(Throwable $e, int $httpCode): void
    {
        $message = $e->getMessage();
        $context = Log::getFullContext();
        $error = $message . ' - ' . $context;
        Log::addErrorLog($error);
        Http::sendStatusResponse($httpCode, $message);
        exit(1);
    }

    #[NoReturn] public static function handleSuccessAndQuit(int $httpCode, string $status, $data = null): void
    {

        if ($data !== null) {
            Http::sendDataResponse($httpCode, $data);
        } else {
            Http::sendStatusResponse($httpCode, $status);
        }
        Log::addInfoLog($status);
        exit(0);
    }
}