<?php

namespace Controller;

interface ControllerInterface
{
    public function validatePostData(mixed $data): bool;

    public function validatePutData(mixed $data): bool;
}