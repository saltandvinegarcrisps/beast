<?php

namespace Tests;

use Beast\Framework\Config\FileLoader;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $path;

    public function setup(): void
    {
        $this->path = __DIR__ . '/resources';
    }

    public function testGet(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals('banana', $config->get('sample-config.apple'));
        $this->assertEquals('broccoli', $config->get('sample-config.asparagus'));
    }
}
