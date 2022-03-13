<?php

namespace Beast\Framework\Support;

use InvalidArgumentException;

class Paths
{
    protected $path;

    public function __construct(string $path)
    {
        $this->path = realpath($path);

        if (false === $this->path) {
            throw new InvalidArgumentException(
                'Path dir not found: '.$path
            );
        }
    }

    public function resolve(string $relative): string
    {
        return $this->path . '/' . $relative;
    }
}
