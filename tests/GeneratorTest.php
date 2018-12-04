<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Beast\Framework\Tokens\Generator;

class GeneratorTest extends TestCase
{
    public function testCreate()
    {
        $generator = new Generator;
        $result = $generator->create();
        $this->assertEquals(64, strlen($result));
    }
}
