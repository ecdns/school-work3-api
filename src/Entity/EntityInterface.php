<?php

namespace Entity;


interface EntityInterface
{
    public function toArray(): array;

    public function toJson(): string;
}