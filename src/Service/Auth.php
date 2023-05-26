<?php

declare(strict_types=1);

namespace Service;

use Doctrine\DBAL\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isValidPassword(string $password, string $hashedPassword): bool
    {
        if (password_verify($password, $hashedPassword)) {
            return true;
        } else {
            return false;
        }
    }

    public function encodeJWT($email, $jwtKey): string
    {
        $issuedAt = time();
        $key = $jwtKey;
        $alg = 'HS256';
        $payload = array(
            'userid' => $email,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 86400
        );
        return JWT::encode($payload, $key, $alg);
    }

    /**
     * @throws \Exception
     */
    public function authenticateRequestToken(string $jwtKey, string $jwt): bool
    {
        return $this->decodeJWT($jwtKey, $jwt);
    }

    /**
     * @throws \Exception
     */
    private function decodeJWT($jwtKey, $jwt): bool
    {
        try {
            JWT::decode($jwt, new Key($jwtKey, 'HS256'));
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

}