<?php

declare(strict_types=1);

namespace Controller;

abstract class AbstractController
{

    public function validatePostData(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }
        return true;
    }

    public function validatePutData(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $key) {
            if (isset($data[$key])) {
                return true;
            }
        }
        return false;
    }
}