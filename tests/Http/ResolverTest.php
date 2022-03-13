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
    /**
     * @dataProvider routeDataProvider
     * @param  Route $route
     * @return void
     */
    public function testResolvesToSingleActionController(Route $route): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);

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

    public function routeDataProvider(): array
    {
        return [
            'Resolves single-action controller' => [
                new Route('GET', '/foo', ExampleController::class),
            ],
            'Resolves array callable' => [
                new Route('GET', '/foo', [ExampleController::class, 'index']),
            ],
        ];
    }
}
