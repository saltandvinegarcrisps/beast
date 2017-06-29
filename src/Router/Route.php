<?php

namespace Beast\Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

class Route
{
    protected $path = '/';

    protected $controller;

    protected $method = 'GET';

    protected $params = [];

    protected $arguments = [];

    protected $description = '';

    public function __construct(string $method, string $path, $controller)
    {
        $this->setMethod($method);
        $this->setPath($path);
        $this->setController($controller);
        $this->params = [];
    }

    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
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

    protected function tokenise(): array
    {
        $pattern = '#\{([A-z0-9\-_]+)\}#';

        $tokens = [];

        if (preg_match_all($pattern, $this->path, $matches)) {
            $tokens = $matches[1];
        }

        $path = preg_replace($pattern, '([^/]+)', $this->path);

        return [$path, $tokens];
    }

    public function matches(ServerRequestInterface $request): bool
    {
        if ($this->method != $request->getMethod()) {
            return false;
        }

        $url = $request->getUri()->getPath();

        list($path, $tokens) = $this->tokenise();

        if (! preg_match('#^'.$path.'$#', $url, $matches)) {
            return false;
        }

        $this->params = array_combine($tokens, array_slice($matches, 1));

        return true;
    }
}
