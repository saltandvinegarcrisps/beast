<?php

namespace Beast\Framework\Http;

use Beast\Framework\Router\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResolverInterface
{
    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Route $route
    ): ResponseInterface;
}
