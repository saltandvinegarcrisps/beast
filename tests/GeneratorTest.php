<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Tokens\Generator;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    public function testCreate(): void
    {
        $generator = new Generator;
        $result = $generator->create();
        $this->assertEquals(64, \strlen($result));
    }
}
