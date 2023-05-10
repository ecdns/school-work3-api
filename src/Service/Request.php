<?php

declare(strict_types=1);

namespace Service;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

class Request
{

    private Http $http;
    private Log $log;

    public function __construct(Http $http, Log $log)
    {
        $this->http = $http;
        $this->log = $log;
    }

    #[NoReturn] public function handleErrorAndQuit(int $httpCode, Throwable $e): void
    {
        $message = $e->getMessage();
        $this->http->sendStatusResponse($httpCode, $message);
        $context = $this->log->getContext();
        $error = $context . ' - ' . $message;
        $this->log->addErrorLog($error);
        exit(1);
    }

    #[NoReturn] public function handleSuccessAndQuit(int $httpCode, string $status, $data = null): void
    {

        if ($data !== null) {
            $this->http->sendDataResponse($httpCode, $data);
        } else {
            $this->http->sendStatusResponse($httpCode, $status);
        }
        $context = $this->log->getContext();
        $success = $context . ' - ' . $status;
        $this->log->addSuccessLog($success);
        exit(0);
    }
}