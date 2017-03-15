<?php

namespace Beast\Framework\Support;

class Paths
{
    protected $root;

    public function __construct($root)
    {
        $this->root = realpath($root);
    }

    public function resolve(string $relative): string
    {
        return $this->root . '/' . $relative;
    }
}
