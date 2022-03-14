<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Router\Route;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class RouteTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $method = 'GET';
        $path = '/';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $this->assertEquals($path, $route->getPath());
        $result = $route->setPath($path);
        $this->assertEquals($route, $result);

        $this->assertEquals($method, $route->getMethod());
        $result = $route->setMethod($method);
        $this->assertEquals($route, $result);

        $this->assertEquals($callback, $route->getController());
        $result = $route->setController($callback);
        $this->assertEquals($route, $result);
    }

    public function testSimpleMatch(): void
    {
        $method = 'GET';
        $path = '/';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
    }

    public function testNoMatchMethod(): void
    {
        $method = 'GET';
        $path = '/';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertFalse($result);
    }

    public function testNoMatch(): void
    {
        $method = 'GET';
        $path = '/';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertFalse($result);
    }

    public function testTokenMatch(): void
    {
        $method = 'GET';
        $path = '/token/{token}';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/token/abc123!"£$%^&*()-_=+');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
        $this->assertEquals([
            'token' => 'abc123!"£$%^&*()-_=+',
        ], $route->getParams());
    }

    public function testTokenMatchFailure(): void
    {
        $method = 'GET';
        $path = '/token/{token';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/token/abc');
        $request->method('getUri')->willReturn($uri);

        $this->expectException(RuntimeException::class);
        $route->matches($request);
    }

    public function testTypedTokenMatch(): void
    {
        $method = 'GET';
        $path = '/num/{num:num}/alpha/{alpha:alpha}/alnum/{alnum:alnum}/slug/{slug:slug}';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/num/1234/alpha/abcd/alnum/a1b2c3/slug/some-blog_title');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
        $this->assertEquals([
            'num' => '1234',
            'alpha' => 'abcd',
            'alnum' => 'a1b2c3',
            'slug' => 'some-blog_title',
        ], $route->getParams());
    }

    public function testEnumTokenMatch(): void
    {
        $method = 'GET';
        $path = '/enum/{enum:"foo,bar"}';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        // ---

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/enum/foo');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
        $this->assertEquals([
            'enum' => 'foo',
        ], $route->getParams());

        // ---

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/enum/bar');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
        $this->assertEquals([
            'enum' => 'bar',
        ], $route->getParams());

        // ---

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/enum/baz');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertFalse($result);
    }

    public function testWildcardTokenMatch(): void
    {
        $method = 'GET';
        $path = '/{path:*}';
        $callback = 'callback';
        $route = new Route($method, $path, $callback);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/once/upon/a/time/in/devland');
        $request->method('getUri')->willReturn($uri);

        $result = $route->matches($request);
        $this->assertTrue($result);
        $this->assertEquals([
            'path' => 'once/upon/a/time/in/devland',
        ], $route->getParams());
    }
}
