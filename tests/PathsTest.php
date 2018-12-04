<?php

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Beast\Framework\Support\Paths;

class PathsTest extends TestCase
{
    public function testResolve()
    {
        $paths = new Paths(sys_get_temp_dir());
        $this->assertEquals(sys_get_temp_dir().'/foo/bar.json', $paths->resolve('foo/bar.json'));
    }

    public function testMissingDir()
    {
        $path = '/some/path/that/fails';
        $this->expectException(InvalidArgumentException::class);
        new Paths($path);
    }
}
