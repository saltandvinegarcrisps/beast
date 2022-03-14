<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Http\SapiEmitter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class SapiEmitterTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEmit(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaders')->willReturn(['content-type' => ['text/plain']]);
        $response->method('getBody')->willReturn('Hello World');
        $emitter = new SapiEmitter();
        $this->expectOutputString('Hello World');
        $emitter->emit($response);
    }
}
