<?php

namespace Beast\Framework\Http\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Tari\ServerMiddlewareInterface;
use Tari\ServerFrameInterface;

use Beast\Framework\Tokens\StorageInterface;

class Csrf implements ServerMiddlewareInterface
{
    protected $storage;

    protected $inputFieldName;

    protected $headerFieldName;

    public function __construct(StorageInterface $storage, string $inputFieldName = 'csrf_token', string $headerFieldName = 'X-Csrf-Token')
    {
        $this->storage = $storage;
        $this->inputFieldName = $inputFieldName;
        $this->headerFieldName = $headerFieldName;
    }

    protected function isMethod(ServerRequestInterface $request): bool
    {
        return in_array($request->getMethod(), [
            'POST',
            'PUT',
            'PATCH',
        ]);
    }

    protected function isValid(ServerRequestInterface $request): bool
    {
        $token = $this->extractToken($request);

        return $this->storage->has($token);
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

        return '';
    }

    public function handle(ServerRequestInterface $request, ServerFrameInterface $frame): ResponseInterface
    {
        if (! $this->isMethod($request) || ($this->isMethod($request) && $this->isValid($request))) {
            return $frame->next($request);
        }

        return $frame->factory()->createResponse(400, [], 'Invalid CSRF Token');
    }
}
