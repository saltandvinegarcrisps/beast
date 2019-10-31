<?php

namespace Beast\Framework\Router;

use BadMethodCallException;
use Countable;
use Iterator;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;
use function array_pop;
use function implode;
use function in_array;
use function is_string;
use function rtrim;
use function strtoupper;

/**
 * Class Routes
 *
 * @package Beast\Framework\Router
 * @method Route any(string $method, array $args)
 * @method Route connect(string $method, array $args)
 * @method Route trace(string $method, array $args)
 * @method Route get(string $method, array $args)
 * @method Route head(string $method, array $args)
 * @method Route options(string $method, array $args)
 * @method Route post(string $method, array $args)
 * @method Route put(string $method, array $args)
 * @method Route patch(string $method, array $args)
 * @method Route delete(string $method, array $args)
 */
class Routes implements Countable, IteratorAggregate
{
    protected $routes;

    protected $segments;

    protected $namespaces;

    public function __construct(array $routes = [])
    {
        $this->routes = new SplObjectStorage;
        foreach ($routes as $route) {
            $this->append($route);
        }
        $this->segments = [];
        $this->namespaces = [];
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

        if (isset($options['namespace'])) {
            $this->namespaces[] = '\\'.$options['namespace'];
        }
    }

    public function removeOptions(array $options): void
    {
        if (isset($options['prefix'])) {
            array_pop($this->segments);
        }

        if (isset($options['namespace'])) {
            array_pop($this->namespaces);
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

    public function getNamespace(): string
    {
        return implode('', $this->namespaces) . '\\';
    }

    public function append(Route $route): void
    {
        $this->routes->attach($route);
    }

    public function create(string $method, string $path, $controller): Route
    {
        return new Route(
            $method,
            $this->getPrefix().$path,
            is_string($controller) ? $this->getNamespace().$controller : $controller
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

        if (!in_array($method, $allowed, true)) {
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
