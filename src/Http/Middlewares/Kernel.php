<?php

namespace Beast\Framework\Http\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Beast\Framework\Router\Routes;
use Beast\Framework\Http\ResolverInterface;

class Kernel implements MiddlewareInterface
{
    private $routes;

    private $resolver;

    public function __construct(Routes $routes, ResolverInterface $resolver)
    {
        $this->routes = $routes;
        $this->resolver = $resolver;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $route = $this->routes->match($request);

        return $this->resolver->resolve(
            $request,
            $handler->handle($request),
            $route
        );
    }
}
