<?php

namespace Beast\Framework\Database;

interface EntityInterface
{
    /**
     * @param  array<string, int|string|null> $attributes
     * @return EntityInterface
     */
    public function withAttributes(array $attributes): EntityInterface;

    /**
     * @param  array<string, int|string|null> $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void;

    /**
     * @return array<string, int|string|null>
     */
    public function getAttributes(): array;

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array;

    public function toJson(): string;
}
