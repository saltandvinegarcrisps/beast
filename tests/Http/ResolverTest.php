<?php

namespace Beast\Framework\Tests\Http;

use Beast\Framework\Http\Resolver;
use Beast\Framework\Router\Route;
use Beast\Framework\Tests\Fixtures\ExampleController;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ResolverTest extends TestCase
{
    public function testResolvesToSingleActionController(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $route = new Route('GET', '/foo', ExampleController::class);

        $containerMock->expects($this->once())
            ->method('get')
            ->with(ExampleController::class)
            ->willReturn(new ExampleController())
        ;

        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock)
        ;

        $streamMock->expects($this->once())
            ->method('write')
            ->with('Hello World');

        $resolver = new Resolver($containerMock);

        $resolver->resolve($requestMock, $responseMock, $route);
    }
}
