<?php

declare(strict_types=1);

namespace Controller;

/**
 * @OA\Info(
 *     title="Cube-3 API",
 *     version="1.0.0",
 *     @OA\Contact(
 *     email="leo.paillard@gmail.com"
 *    )
 * )
 * @OA\Server(
 *     description="Cube-3 API",
 *     url="http://cyber-dodo.fr/api/v1"
 * )
 */
abstract class AbstractController
{

    public function validateData(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }
        return true;
    }

    public function validateDataUpdate(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $key) {
            if (isset($data[$key])) {
                return true;
            }
        }
        return false;
    }
}