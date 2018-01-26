<?php

namespace Beast\Framework\Http;

use Psr\Container\ContainerInterface;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Beast\Framework\Router\Route;
use Beast\Framework\Router\Routes;
use Beast\Framework\Router\RouteNotFoundException;

use Beast\Framework\Support\ContainerAwareInterface;

class Kernel implements MiddlewareInterface
{
    private $container;

    private $routes;

    public function __construct(ContainerInterface $container, Routes $routes)
    {
        $this->container = $container;
        $this->routes = $routes;
    }

    private function controller(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
        string $controller
    ) {
        list($class, $method) = explode('@', $controller, 2);

        $instance = $this->container->get($class);

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance->$method($request, $response, $args);
    }

    private function run(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Route $route,
        $callable
    ) {
        if (is_string($callable) && strpos($callable, '@')) {
            return $this->controller($request, $response, $route->getParams(), $callable);
        }

        return $callable->bindTo($this->container)($request, $response, $route->getParams());
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $route = $this->routes->match($request);
        } catch (RouteNotFoundException $e) {
            $this->response->getBody()->write($e->getMessage());
            return $this->response->withStatus(404);
        }

        $callable = $route->getController();

        $response = $this->run($request, $handler->handle($request), $route, $callable);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        $this->response->getBody()->write($response);

        return $this->response->withStatus(200);
    }
}
