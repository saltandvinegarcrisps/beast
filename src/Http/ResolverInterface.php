<?php

namespace Beast\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Beast\Framework\Router\Route;

interface ResolverInterface
{
    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Route $route
    ): ResponseInterface;
}
