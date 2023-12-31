<?php

declare(strict_types=1);

namespace Service;

use Entity\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private Request $request;
    private DAO $dao;
    private string $jwtKey;

    public function __construct(Request $request, DAO $dao, string $jwtKey)
    {
        $this->request = $request;
        $this->dao = $dao;
        $this->jwtKey = $jwtKey;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isValidPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
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
     * @throws Exception
     */
    public function authenticateRequestToken(string $jwtKey, string $jwt): bool
    {
        return $this->decodeJWT($jwtKey, $jwt);
    }

    private function decodeJWT($jwtKey, $jwt): bool
    {
        try {
            JWT::decode($jwt, new Key($jwtKey, 'HS256'));
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    public function processAuthentication(): void
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        if ($token) {

            $token = explode(' ', $token)[1];

            $isTokenValid = $this->authenticateRequestToken($this->jwtKey, $token);

            if (!$isTokenValid) {
                $this->request->handleErrorAndQuit(401, new Exception('Unauthorized'));
            }

        } else {
            $this->request->handleErrorAndQuit(401, new Exception('Unauthorized'));
        }

        try {
            $user = $this->dao->getOneBy(User::class, ['jwt' => $token]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(401, new Exception('Unauthorized'));
        }

        if (!$user) {
            $this->request->handleErrorAndQuit(401, new Exception('Unauthorized'));
        }
    }

    public function authenticateRequest(array $requestInfo): void
    {
        if ($requestInfo[1][1] != 'loginUser' && $requestInfo[1][1] != 'getDocumentation') $this->processAuthentication();
    }

}