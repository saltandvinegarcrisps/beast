<?php

namespace Beast\Framework\Http;

use Beast\Framework\Router\Route;

use Beast\Framework\Support\ContainerAwareInterface;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Resolver implements ResolverInterface
{
    /**
     * @var ContainerInterface
     */
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

        // Single Action Controllers (Invokable Controllers)
        if (\is_string($controller) && class_exists($controller)) {
            $instance = $this->container->get($controller);

            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($this->container);
            }

            if (\is_callable($instance)) {
                return $instance($request, $response, $route->getParams());
            }
        }

        if (\is_array($controller) && \count($controller) === 2) {
            [$class, $method] = $controller;

            $instance = $this->container->get($class);

            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($this->container);
            }

            return $instance->$method($request, $response, $route->getParams());
        }

        if ($controller instanceof Closure) {
            $closure = $controller->bindTo($this->container);
            if ($closure !== null) {
                return $closure($request, $response, $route->getParams());
            }
        }

        throw new InvalidArgumentException('controller must be a Closure, array or class-string');
    }
}
