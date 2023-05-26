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

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function allowCors(): void
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 1000');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
            }
            exit(0);
        }
    }

}