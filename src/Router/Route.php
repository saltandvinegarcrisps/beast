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

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string>
     */
    protected $controller;

    /**
     * @var array<int|string, mixed>|false
     */
    protected $params = [];

    /**
     * @param string $method
     * @param string $path
     * @param class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     */
    public function __construct(string $method, string $path, $controller)
    {
        $this->setMethod($method);
        $this->setPath($path);
        $this->setController($controller);
        $this->params = [];
    }

    /**
     * @return array<int|string, mixed>|false
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath(string $path)
    {
        $this->path = rtrim($path, '/') ?: '/';

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string> $controller
     * @return self
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return class-string|callable(\Psr\Http\Message\ServerRequestInterface, \Psr\Http\Message\ResponseInterface, array<string, int|string|array<mixed>>): void|non-empty-array<class-string, string>
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $method
     * @return self
     */
    public function setMethod(string $method)
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

        if (\is_string($path) && ! preg_match('~^'.$path.'$~', $url, $matches)) {
            return false;
        }

        if (!isset($matches) || !\is_array($matches)) {
            return false;
        }

        if (!\is_array($tokens)) {
            return false;
        }

        $this->params = array_combine($tokens, \array_slice($matches, 1));

        return true;
    }
}
