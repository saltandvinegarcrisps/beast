<?php

namespace Beast\Framework\Router;

use BadMethodCallException;
use Countable;
use Iterator;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

/**
 * Class Routes
 *
 * @package Beast\Framework\Router
 *
 * @implements \IteratorAggregate<int, Route>
 *
 */
class Routes implements Countable, IteratorAggregate
{
    /**
     * @var \SplObjectStorage<Route, null>
     */
    protected $routes;

    /**
     * @var array<int, string>
     */
    protected $segments;

    /**
     * @param array<int, Route> $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = new SplObjectStorage;
        foreach ($routes as $route) {
            $this->append($route);
        }
        $this->segments = [];
    }

    /**
     * @return \SplObjectStorage<Route, null>
     */
    public function getIterator(): Iterator
    {
        return $this->routes;
    }

    public function count(): int
    {
        return $this->routes->count();
    }

    /**
     * @param array<string, int|string> $options
     */
    public function addOptions(array $options): void
    {
        if (isset($options['prefix']) && \is_string($options['prefix'])) {
            $this->segments[] = rtrim($options['prefix'], '/');
        }
    }

    /**
     * @param array<string, int|string> $options
     * @return void
     */
    public function removeOptions(array $options): void
    {
        if (isset($options['prefix'])) {
            array_pop($this->segments);
        }
    }

    /**
     * @param array<string, int|string> $options
     * @param callable(self $routes): void $group
     * @return void
     */
    public function group(array $options, callable $group): void
    {
        $this->addOptions($options);

        $group($this);

        $this->removeOptions($options);
    }

    public function getPrefix(): string
    {
        return implode('', $this->segments);
    }

    public function append(Route $route): void
    {
        $this->routes->attach($route);
    }

    /**
     * Create a new Route Definition
     *
     * @param  string $method
     * @param  string $path
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function create(string $method, string $path, $controller): Route
    {
        return new Route(
            $method,
            $this->getPrefix().$path,
            $controller
        );
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function any(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_ANY, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function connect(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_CONNECT, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function trace(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_TRACE, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function get(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_GET, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function head(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_HEAD, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function options(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_OPTIONS, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function post(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_POST, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function put(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_PUT, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function patch(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_PATCH, $path, $controller);

        $this->append($route);

        return $route;
    }

    /**
     * @param  class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return Route
     */
    public function delete(string $path, $controller)
    {
        $route = $this->create(Route::METHOD_DELETE, $path, $controller);

        $this->append($route);

        return $route;
    }

    public function match(ServerRequestInterface $request): Route
    {
        foreach ($this as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }

        throw new RouteNotFoundException('Route not found: '.$request->getUri()->getPath());
    }
}
