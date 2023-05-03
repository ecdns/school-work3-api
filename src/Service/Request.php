<?php

declare(strict_types=1);

namespace Service;

use Throwable;

class Request
{
    public static function handleErrorAndQuit(Throwable $e, int $httpCode): void
    {
        $message = $e->getMessage();
        $context = Log::getFullContext();
        $error = $context . ' - ' . $message;
        Log::addErrorLog($error);
        Http::sendStatusResponse($httpCode, $message);
        exit(1);
    }

    public static function handleSuccessAndQuit(int $httpCode, string $status, $data = null): void
    {

        if ($data !== null) {
            Http::sendDataResponse($httpCode, $data);
        } else {
            Http::sendStatusResponse($httpCode, $status);
        }
        $context = Log::getContext();
        $success = $context . ' - ' . $status;
        Log::addInfoLog($success);
        exit(0);
    }
}