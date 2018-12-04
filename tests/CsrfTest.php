<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Beast\Framework\Http\Middlewares\Csrf;
use Beast\Framework\Tokens\StorageInterface;

class CsrfTest extends TestCase
{
    public function testProcessHeader()
    {
        $response = $this->createMock(ResponseInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('validate')->willReturn(true);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('hasHeader')->willReturn(true);
        $request->method('getHeaderLine')->willReturn('token');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $csrf = new Csrf($response, $storage);
        $result = $csrf->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessBody()
    {
        $response = $this->createMock(ResponseInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('validate')->willReturn(true);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('hasHeader')->willReturn(false);
        $request->method('getParsedBody')->willReturn(['csrf_token' => 'token']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $csrf = new Csrf($response, $storage);
        $result = $csrf->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
