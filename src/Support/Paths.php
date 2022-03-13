<?php

namespace Beast\Framework\Support;

use InvalidArgumentException;

class Paths
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path)
    {
        $path = realpath($path);

        if (false === $path) {
            throw new InvalidArgumentException(
                'Path dir not found: '.$path
            );
        }

        $this->path = $path;
    }

    public function resolve(string $relative): string
    {
        return $this->path . '/' . $relative;
    }
}
