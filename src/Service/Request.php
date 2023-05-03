<?php

declare(strict_types=1);

namespace Service;

use Throwable;

class Request
{
    public static function handleErrorAndQuit(int $httpCode, Throwable $e): void
    {
        $message = $e->getMessage();
        Http::sendStatusResponse($httpCode, $message);
        $context = Log::getContext();
        $error = $context . ' - ' . $message;
        Log::addErrorLog($error);
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
        Log::addSuccessLog($success);
        exit(0);
    }
}