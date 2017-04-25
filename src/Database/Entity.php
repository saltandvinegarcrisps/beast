<?php

namespace Beast\Framework\Database;

abstract class Entity implements EntityInterface
{
    protected $attributes = [];

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $key)
    {
        if (! array_key_exists($key, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('Undefined attribute %s on %s', $key, get_class($this)));
        }
        return $this->attributes[$key];
    }

    public function __set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key)
    {
        return array_key_exists($key, $this->attributes);
    }

    public function __unset(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        }
    }

    public function withAttributes(array $attributes): EntityInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toArray(): array
    {
        return array_diff_key($this->getAttributes(), array_fill_keys($this->guarded, null));
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
