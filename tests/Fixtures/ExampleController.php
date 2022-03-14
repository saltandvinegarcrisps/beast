<?php

namespace Beast\Framework\Tests\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExampleController
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write('Hello World');

        return $response;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->__invoke($request, $response);
    }
}
