<?php

namespace Controller;

interface ControllerInterface
{
    public function validateData(mixed $data, bool $isPostRequest = true): bool;
}