<?php

namespace Service;

abstract class HttpHelper
{
    public static function sendStatusResponse(int $httpCode, mixed $state): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode(['result' => $state]);

    }

    public static function sendDataResponse(int $httpCode, mixed $data): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($data);
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