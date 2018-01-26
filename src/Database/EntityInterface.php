<?php

namespace Beast\Framework\Database;

interface EntityInterface
{
    public function withAttributes(array $attributes): EntityInterface;

    public function setAttributes(array $attributes);

    public function getAttributes(): array;

    public function toArray(): array;

    public function toJson(): string;
}
