<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Config\ConfigException;
use Beast\Framework\Config\JsonFileLoader as FileLoader;
use PHPUnit\Framework\TestCase;

class ConfigJsonTest extends TestCase
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
    }

    public function testGetNested(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals(['dewberry' => 'elderberry'], $config->get('sample-config.cherry'));
    }

    public function testGetMissing(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals(null, $config->get('sample-config.snozzberry'));
    }

    public function testMissingDir(): void
    {
        $path = '/some/path/that/fails';
        $this->expectException(ConfigException::class);
        new FileLoader($path);
    }

    public function testMissingFile(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals(null, $config->get('fail'));
    }

    public function testInvalidName(): void
    {
        $config = new FileLoader($this->path);
        $this->expectException(ConfigException::class);
        $config->get('');
    }
}
