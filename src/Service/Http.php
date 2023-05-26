<?php

declare(strict_types=1);

namespace Service;

class Http
{
    public function sendStatusResponse(int $httpCode, mixed $state): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode(['result' => $state]);
    }

    public function sendDataResponse(int $httpCode, mixed $data): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getAuthHeaderValue(array $headers): string|false
    {
        if (isset($headers['Authorization'])) {
            $bearerToken = $headers['Authorization'];
            return explode(' ', $bearerToken)[1];
        } else {
            return false;
        }
    }

}