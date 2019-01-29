<?php

namespace Beast\Framework\Http\Middlewares;

use Beast\Framework\Tokens\StorageInterface;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Psr\Http\Server\RequestHandlerInterface;

class Csrf implements MiddlewareInterface
{
    protected $response;

    protected $storage;

    protected $inputFieldName;

    protected $headerFieldName;

    public function __construct(
        ResponseInterface $response,
        StorageInterface $storage,
        string $inputFieldName = 'csrf_token',
        string $headerFieldName = 'X-Csrf-Token'
    ) {
        $this->response = $response;
        $this->storage = $storage;
        $this->inputFieldName = $inputFieldName;
        $this->headerFieldName = $headerFieldName;
    }

    protected function isMethod(ServerRequestInterface $request): bool
    {
        return \in_array($request->getMethod(), [
            'POST',
            'PUT',
            'PATCH',
        ]);
    }

    protected function isValid(ServerRequestInterface $request): bool
    {
        $token = $this->extractToken($request);

        return $this->storage->validate($token);
    }

    protected function extractToken(ServerRequestInterface $request): string
    {
        if ($request->hasHeader($this->headerFieldName)) {
            return $request->getHeaderLine($this->headerFieldName);
        }

        $input = $request->getParsedBody();

        if (isset($input[$this->inputFieldName])) {
            return $input[$this->inputFieldName];
        }

        throw new InvalidArgumentException(\sprintf(
            'Csrf token not found in %s header or %s body',
            $this->headerFieldName,
            $this->inputFieldName
        ));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isMethod($request) || ($this->isMethod($request) && $this->isValid($request))) {
            return $handler->handle($request);
        }

        $this->response->getBody()->write('Invalid CSRF Token');

        return $this->response->withStatus(400);
    }
}
