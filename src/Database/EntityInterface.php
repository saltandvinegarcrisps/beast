<?php

namespace Beast\Framework\Database;

interface EntityInterface
{
    public function withAttributes(array $attributes): EntityInterface;

    public function getAttributes(): EntityInterface;
}
