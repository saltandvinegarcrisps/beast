<?php

namespace Tests;

use Beast\Framework\Support\Paths;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PathsTest extends TestCase
{
    public function testResolve(): void
    {
        $paths = new Paths(\sys_get_temp_dir());

        $this->assertEquals(\realpath(\sys_get_temp_dir()).'/foo/bar.json', $paths->resolve('foo/bar.json'));
    }

    public function testMissingDir(): void
    {
        $path = '/some/path/that/fails';
        $this->expectException(InvalidArgumentException::class);
        new Paths($path);
    }
}
