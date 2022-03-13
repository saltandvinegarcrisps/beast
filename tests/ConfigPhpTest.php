<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Config\ConfigException;
use Beast\Framework\Config\PhpFileLoader as FileLoader;
use PHPUnit\Framework\TestCase;

class ConfigPhpTest extends TestCase
{
    private $path;

    public function setup(): void
    {
        $this->path = __DIR__ . '/resources';
    }

    public function testGet(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals('broccoli', $config->get('sample-config.asparagus'));
    }

    public function testGetNested(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals(['dandelion' => 'endive'], $config->get('sample-config.cabbage'));
    }

    public function testGetMissing(): void
    {
        $config = new FileLoader($this->path);
        $this->assertEquals(null, $config->get('sample-config.mandrake'));
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
