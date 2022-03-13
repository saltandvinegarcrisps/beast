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
 * @method Route any(string $path, array|Closure|string $args)
 * @method Route connect(string $path, array|Closure|string $args)
 * @method Route trace(string $path, array|Closure|string $args)
 * @method Route get(string $path, array|Closure|string $args)
 * @method Route head(string $path, array|Closure|string $args)
 * @method Route options(string $path, array|Closure|string $args)
 * @method Route post(string $path, array|Closure|string $args)
 * @method Route put(string $path, array|Closure|string $args)
 * @method Route patch(string $path, array|Closure|string $args)
 * @method Route delete(string $path, array|Closure|string $args)
 */
class Routes implements Countable, IteratorAggregate
{

    protected $routes;

    /**
     * Holds the prefix URL path for the given Route Collection
     * 
     * @var array<int, string>
     */
    protected $segments = [];


    /**
     * Scope the Route Collection to a specific namespace so you can reference a relative Controller
     * 
     * @var array<int, string>
     */
    protected $namespaces = [];

    /**
     * Holds specific middleware for a given Route Collection
     * 
     * @var array<int, class-string>
     */
    protected $middleware = [];

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
            $this->segments[] = \rtrim($options['prefix'], '/');
        }

        if (isset($options['namespace'])) {
            $this->namespaces[] = '\\'.$options['namespace'];
        }

        if (isset($options['middleware'])) {
            $this->middleware[] = $options['middleware'];
        }
    }

    public function removeOptions(array $options): void
    {
        if (isset($options['prefix'])) {
            \array_pop($this->segments);
        }

        if (isset($options['namespace'])) {
            \array_pop($this->namespaces);
        }

        if (isset($options['middleware'])) {
            $this->middleware = [];
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
        return \implode('', $this->segments);
    }

    public function getNamespace(): string
    {
        return \implode('', $this->namespaces) . '\\';
    }

    /**
     * Return middleware associated to the Route Collection
     * 
     * @return array<int, class-string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
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
            \is_string($controller) ? $this->getNamespace().$controller : $controller
        );
    }

    public function __call(string $method, array $args): Route
    {
        $method = \strtoupper($method);

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
