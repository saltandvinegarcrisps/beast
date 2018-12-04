<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Beast\Framework\Http\SapiEmitter;

class SapiEmitterTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEmit()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaders')->willReturn(['content-type' => ['text/plain']]);
        $response->method('getBody')->willReturn('Hello World');
        $emitter = new SapiEmitter();
        $this->expectOutputString('Hello World');
        $emitter->emit($response);
    }
}
