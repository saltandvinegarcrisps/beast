<?php

namespace Beast\Framework\Database;

use ErrorException;
use RuntimeException;

abstract class Entity implements EntityInterface
{
    /**
     * @var array<string, int|string|null>
     */
    protected $attributes = [];

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @param array<string, int|string|null> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * @param  string $key
     * @return int|string|null
     */
    public function __get(string $key)
    {
        if (!\array_key_exists($key, $this->attributes)) {
            throw new ErrorException(sprintf('Undefined attribute "%s" on %s', $key, \get_class($this)));
        }
        return $this->attributes[$key];
    }

    /**
     * @param string $key
     * @param int|string|null $value
     */
    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key)
    {
        return \array_key_exists($key, $this->attributes);
    }

    public function __unset(string $key): void
    {
        if (\array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        }
    }

    /**
     * @param  array<string, int|string|null> $attributes
     * @return EntityInterface
     */
    public function withAttributes(array $attributes): EntityInterface
    {
        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * @param array<string, int|string|null> $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return array_diff_key($this->getAttributes(), array_flip($this->guarded));
    }

    public function toJson(): string
    {
        $encoded = json_encode($this->toArray());

        if (false === $encoded) {
            throw new RuntimeException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return $encoded;
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
