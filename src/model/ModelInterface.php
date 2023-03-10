<?php

namespace Model;

interface ModelInterface
{
    public function fromArray(array $data): self;

    public function toArray(): array;

    public function toJson(): string;
}