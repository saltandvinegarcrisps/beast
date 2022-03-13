<?php

namespace Tests;

use BadMethodCallException;
use Beast\Framework\Router\Route;
use Beast\Framework\Router\RouteNotFoundException;
use Beast\Framework\Router\Routes;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RoutesTest extends TestCase
{
    public function testConstruct(): void
    {
        $route = $this->createMock(Route::class);
        $routes = new Routes([$route]);
        $this->assertCount(1, $routes);
    }

    public function testOptions(): void
    {
        $routes = new Routes();
        $options = [
            'prefix' => 'bar',
        ];
        $routes->addOptions($options);
        $this->assertEquals('bar', $routes->getPrefix());

        $routes->removeOptions($options);
        $this->assertEquals('', $routes->getPrefix());
    }

    public function testGroups(): void
    {
        $route = $this->createMock(Route::class);
        $routes = new Routes([$route]);

        $routes->group([
            'prefix' => 'bar',
        ], function (Routes $r) use ($routes): void {
            $this->assertEquals($routes, $r);
            $this->assertEquals('bar', $routes->getPrefix());
        });
    }

    public function testCreate(): void
    {
        $routes = new Routes();
        $route = $routes->create(Route::METHOD_GET, '/', 'callback');
        $this->assertInstanceof(Route::class, $route);
    }

    public function testCreateByMethod(): void
    {
        $routes = new Routes();
        $route = $routes->any('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->connect('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->trace('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->get('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->head('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->options('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->post('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->put('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->patch('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);

        $routes = new Routes();
        $route = $routes->delete('/', 'callback');
        $this->assertInstanceof(Route::class, $route);
        $this->assertCount(1, $routes);
    }

    public function testCreateByMethodFailure(): void
    {
        $routes = new Routes();

        $this->expectException(BadMethodCallException::class);
        $routes->fail('/', 'callback');
    }

    public function testMatch(): void
    {
        $routes = new Routes();
        $route = $routes->get('/', 'callback');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/');
        $request->method('getUri')->willReturn($uri);

        $result = $routes->match($request);
        $this->assertEquals($route, $result);
    }

    public function testMatchNotFound(): void
    {
        $routes = new Routes();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/');
        $request->method('getUri')->willReturn($uri);

        $this->expectException(RouteNotFoundException::class);
        $routes->match($request);
    }
}
