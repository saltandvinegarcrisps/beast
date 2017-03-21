<?php

namespace Beast\Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

class Routes
{
    protected $routes;

    protected $segments;

    protected $namespaces;

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
        $this->segments = [];
        $this->namespaces = [];
    }

    protected function addOptions(array $options)
    {
        if (isset($options['prefix'])) {
            $this->segments[] = rtrim($options['prefix'], '/');
        }

        if (isset($options['namespace'])) {
            $this->namespaces[] = '\\'.$options['namespace'];
        }
    }

    protected function removeOptions(array $options)
    {
        if (isset($options['prefix'])) {
            array_pop($this->segments);
        }

        if (isset($options['namespace'])) {
            array_pop($this->namespaces);
        }
    }

    public function group(array $options, callable $group)
    {
        $this->addOptions($options);

        $group($this);

        $this->removeOptions($options);
    }

    public function prefix(): string
    {
        return implode('', $this->segments);
    }

    public function namespace(): string
    {
        return implode('', $this->namespaces) . '\\';
    }

    public function append(Route $route)
    {
        $this->routes[] = $route;
    }

    public function create(string $method, string $path, $controller): Route
    {
        return new Route($method, $this->prefix().$path,
            is_string($controller) ? $this->namespace().$controller : $controller);
    }

    public function __call(string $method, array $args): Route
    {
        $methods = ['get', 'head', 'post', 'put', 'delete', 'connect',
            'options', 'trace', 'patch'];

        if (!in_array($method, $methods)) {
            throw new \BadMethodCallException('Method does not exist');
        }

        $route = $this->create(strtoupper($method), $args[0], $args[1]);

        $this->append($route);

        return $route;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(ServerRequestInterface $request): Route
    {
        foreach ($this->routes as $route) {
            if (! $route->matches($request)) {
                continue;
            }

            return $route;
        }

        throw new RouteNotFoundException('route not found');
    }
}
