<?php

namespace Beast\Framework\Http;

use Psr\Container\ContainerInterface;

use Beast\Framework\Support\ContainerAwareInterface;
use Beast\Framework\Router\Route;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Resolver implements ResolverInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Route $route
    ): ResponseInterface {
        $controller = $route->getController();

        if (is_string($controller) && strpos($controller, '@')) {
            [$class, $method] = explode('@', $controller, 2);

            $instance = $this->container->get($class);

            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($this->container);
            }

            return $instance->$method($request, $response, $route->getParams());
        }

        return $controller->bindTo($this->container)($request, $response, $route->getParams());
    }
}
