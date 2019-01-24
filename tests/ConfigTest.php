<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Beast\Framework\Support\Config;
use Beast\Framework\Support\ConfigException;

class ConfigTest extends TestCase
{
    private $path;

    private $file;

    public function setup()
    {
        $this->path = __DIR__ . '/../tmp';
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
        $this->file = $this->path.'/test.json';
    }

    public function tearDown()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testGet()
    {
        file_put_contents($this->file, '{"foo":"bar"}');
        $config = new Config($this->path);
        $this->assertEquals(['foo' => 'bar'], $config->get('test'));
    }

    public function testGetNested()
    {
        file_put_contents($this->file, '{"foo":{"bar":"baz"}}');
        $config = new Config($this->path);
        $this->assertEquals(['bar' => 'baz'], $config->get('test.foo'));
    }

    public function testGetMissing()
    {
        file_put_contents($this->file, '{"foo":"bar"}');
        $config = new Config($this->path);
        $this->assertNull($config->get('test.baz', null));
    }

    public function testGetEnv()
    {
        putenv('FOOBAR=1234');
        file_put_contents($this->file, '{"foo":"${FOOBAR}"}');
        $config = new Config($this->path);
        $this->assertEquals('1234', $config->get('test.foo'));
    }

    public function testPut()
    {
        file_put_contents($this->file, '{}');
        $config = new Config($this->path);
        $config->put('test.foo.bar', 'baz');
        $this->assertEquals('{
    "foo": {
        "bar": "baz"
    }
}', file_get_contents($this->file));
    }

    public function testMissingDir()
    {
        $path = '/some/path/that/fails';
        $this->expectException(ConfigException::class);
        new Config($path);
    }

    public function testMissingFile()
    {
        $config = new Config($this->path);
        $this->expectException(ConfigException::class);
        $config->get('fail');
    }

    public function testInvalidFile()
    {
        file_put_contents($this->file, '{');
        $config = new Config($this->path);
        $this->expectException(ConfigException::class);
        $config->get('test');
    }

    public function testInvalidName()
    {
        file_put_contents($this->file, '{}');
        $config = new Config($this->path);
        $this->expectException(ConfigException::class);
        $config->get('');
    }
}
