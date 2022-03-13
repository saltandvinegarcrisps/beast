<?php

namespace Beast\Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

class Route
{
    use RouteTokensTrait;

    public const METHOD_ANY = 'ANY';

    public const METHOD_CONNECT = 'CONNECT';

    public const METHOD_TRACE = 'TRACE';

    public const METHOD_GET = 'GET';

    public const METHOD_HEAD = 'HEAD';

    public const METHOD_OPTIONS = 'OPTIONS';

    public const METHOD_POST = 'POST';

    public const METHOD_PUT = 'PUT';

    public const METHOD_PATCH = 'PATCH';

    public const METHOD_DELETE = 'DELETE';

    protected $method;

    protected $path;

    protected $controller;

    protected $params;

    public function __construct(string $method, string $path, $controller)
    {
        $this->setMethod($method);
        $this->setPath($path);
        $this->setController($controller);
        $this->params = [];
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setPath(string $path)
    {
        $this->path = rtrim($path, '/') ?: '/';

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    protected function hasMethod(ServerRequestInterface $request): bool
    {
        return $this->getMethod() === self::METHOD_ANY || $this->getMethod() === $request->getMethod();
    }

    public function matches(ServerRequestInterface $request): bool
    {
        if (!$this->hasMethod($request)) {
            return false;
        }

        $url = $request->getUri()->getPath();

        if (!$this->hasTokens()) {
            return $url === $this->getPath();
        }

        list($path, $tokens) = $this->tokenise();

        if (! preg_match('~^'.$path.'$~', $url, $matches)) {
            return false;
        }

        $this->params = array_combine($tokens, \array_slice($matches, 1));

        return true;
    }
}
