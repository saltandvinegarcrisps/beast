<?php

namespace Beast\Framework\Http;

use Psr\Container\ContainerInterface;

use Tari\ServerMiddlewareInterface;
use Tari\ServerFrameInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Beast\Framework\Support\ContainerAwareInterface;
use Beast\Framework\Router\Route;
use Beast\Framework\Router\Routes;
use Beast\Framework\Router\RouteNotFoundException;

class Kernel implements ServerMiddlewareInterface
{
    private $container;

    private $routes;

    public function __construct(ContainerInterface $container, Routes $routes)
    {
        $this->container = $container;
        $this->routes = $routes;
    }

    private function controller(ServerRequestInterface $request, ServerFrameInterface $frame, Route $route, string $controller)
    {
        list($class, $method) = explode('@', $controller, 2);

        $instance = $this->container->get($class);

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance->$method($request, $frame->next($request), $route->getParams());
    }

    private function run(ServerRequestInterface $request, ServerFrameInterface $frame, Route $route, $callable)
    {
        if (is_string($callable) && strpos($callable, '@')) {
            return $this->controller($request, $frame, $route, $callable);
        }

        return $this->container->call($callable);
    }

    public function handle(ServerRequestInterface $request, ServerFrameInterface $frame): ResponseInterface
    {
        try {
            $route = $this->routes->match($request);
        } catch (RouteNotFoundException $e) {
            return $frame->factory()->createResponse(404, [], $e->getMessage());
        }

        $callable = $route->getController();

        $response = $this->run($request, $frame, $route, $callable);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return $frame->factory()->createResponse(200, [], $response);
    }
}
