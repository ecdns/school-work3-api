<?php

declare(strict_types=1);

namespace Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function isValidPassword(string $password, string $hashedPassword): bool
    {
        if (password_verify($password, $hashedPassword)) {
            return true;
        } else {
            return false;
        }
    }

    public static function encodeJWT($email, $jwtKey): string
    {
        $issuedAt = time();
        $key = $jwtKey;
        $alg = 'HS256';
        $payload = array(
            'userid' => $email,
            'iat' => $issuedAt,
        );
        return JWT::encode($payload, $key, $alg);
    }

    public static function authenticateRequestToken(string $jwtKey, string $jwt): void
    {
        self::decodeJWT($jwtKey, $jwt);
    }

    private static function decodeJWT($jwtKey, $jwt): array
    {
        $decode = JWT::decode($jwt, new Key($jwtKey, 'HS256'));
        return (array)$decode;
    }

}