<?php

namespace Service;

abstract class HttpHelper
{
    public static function setResponse(int $httpCode, mixed $state, bool $sendRequestResultState): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        if ($sendRequestResultState) {
            echo json_encode(['result' => $state]);
        }
    }

    public static function getAuthHeaderValue(array $headers): string|false
    {
        if (isset($headers['Authorization'])) {
            $bearerToken = $headers['Authorization'];
            return explode(' ', $bearerToken)[1];
        } else {
            return false;
        }
    }

}