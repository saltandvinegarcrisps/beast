<?php

namespace Beast\Framework\Router;

use BadMethodCallException;
use Closure;
use Countable;
use Iterator;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

/**
 * Class Routes
 *
 * @package Beast\Framework\Router
 * @method Route any(string $path, array|Closure|class-string $args)
 * @method Route connect(string $path, array|Closure|class-string $args)
 * @method Route trace(string $path, array|Closure|class-string $args)
 * @method Route get(string $path, array|Closure|class-string $args)
 * @method Route head(string $path, array|Closure|class-string $args)
 * @method Route options(string $path, array|Closure|class-string $args)
 * @method Route post(string $path, array|Closure|class-string $args)
 * @method Route put(string $path, array|Closure|class-string $args)
 * @method Route patch(string $path, array|Closure|class-string $args)
 * @method Route delete(string $path, array|Closure|class-string $args)
 */
class Routes implements Countable, IteratorAggregate
{
    protected $routes;

    protected $segments;

    public function __construct(array $routes = [])
    {
        $this->routes = new SplObjectStorage;
        foreach ($routes as $route) {
            $this->append($route);
        }
        $this->segments = [];
    }

    public function getIterator(): Iterator
    {
        return $this->routes;
    }

    public function count(): int
    {
        return $this->routes->count();
    }

    public function addOptions(array $options): void
    {
        if (isset($options['prefix'])) {
            $this->segments[] = rtrim($options['prefix'], '/');
        }
    }

    public function removeOptions(array $options): void
    {
        if (isset($options['prefix'])) {
            array_pop($this->segments);
        }
    }

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
     * @param  array|Closure|class-string $controller
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

    public function __call(string $method, array $args): Route
    {
        $method = strtoupper($method);

        $allowed = [
            Route::METHOD_ANY,
            Route::METHOD_CONNECT,
            Route::METHOD_TRACE,
            Route::METHOD_GET,
            Route::METHOD_HEAD,
            Route::METHOD_OPTIONS,
            Route::METHOD_POST,
            Route::METHOD_PUT,
            Route::METHOD_PATCH,
            Route::METHOD_DELETE,
        ];

        if (!\in_array($method, $allowed, true)) {
            throw new BadMethodCallException('Invalid HTTP Method: '.$method);
        }

        $route = $this->create($method, $args[0], $args[1]);

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
