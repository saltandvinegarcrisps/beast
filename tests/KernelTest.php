<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Http\Middlewares\Kernel;
use Beast\Framework\Http\Resolver;
use Beast\Framework\Router\Route;
use Beast\Framework\Router\Routes;
use Beast\Framework\Support\ContainerAwareInterface;

use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class KernelTest extends TestCase
{
    public function testProcessCallable(): void
    {
        $route = $this->createMock(Route::class);
        $route->method('getController')->willReturn(function ($request, $response, $args) {
            return $response;
        });

        $routes = $this->createMock(Routes::class);
        $routes->method('match')->willReturn($route);

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $resolver = $this->createMock(Resolver::class);
        $resolver->method('resolve')->willReturn($response);

        $kernel = new Kernel($routes, $resolver);
        $result = $kernel->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessController(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(new class() implements ContainerAwareInterface {
            protected $container;
            public function setContainer(ContainerInterface $container): void
            {
                $this->container = $container;
            }
            public function getContainer(): ContainerInterface
            {
                return $this->container;
            }
            public function bar($request, $response, $args)
            {
                return $response;
            }
        });

        $route = $this->createMock(Route::class);
        $route->method('getController')->willReturn('foo@bar');

        $routes = $this->createMock(Routes::class);
        $routes->method('match')->willReturn($route);

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $resolver = $this->createMock(Resolver::class);
        $resolver->method('resolve')->willReturn($response);

        $kernel = new Kernel($routes, $resolver);
        $result = $kernel->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
