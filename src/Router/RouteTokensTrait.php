<?php

namespace Beast\Framework\Router;

use RuntimeException;

trait RouteTokensTrait
{
    abstract public function getPath(): string;

    protected function hasTokens(): bool
    {
        $path = $this->getPath();
        // contains tokens
        return strpos($path, '{') !== false ||
            // contains captures
            strpos($path, '(') !== false ||
            // contains groups
            strpos($path, '[') !== false;
    }

    /**
     * @return array<int, bool|float|int|string|array<int|string>>
     */
    protected function tokenise(): array
    {
        $pattern = '~\{([A-z0-9\-_]+)(:([A-z0-9",\-_\*]+))?\}~';

        if (preg_match_all($pattern, $this->getPath(), $matches)) {
            // named parameters to combine when matched with a URL
            $tokens = $matches[1];

            // path to transform
            $path = $this->getPath();

            // replace named parameters with valid regex
            $path = $this->parameterise($matches, $path);

            return [$path, $tokens];
        }

        throw new RuntimeException('No tokens found');
    }

    /**
     * @param  array<int, array<int, string>> $matches
     * @param  string $path
     * @return string
     */
    protected function parameterise(array $matches, string $path): string
    {
        $pattern = function ($token): string {
            $map = [
                'num' => '([0-9]+)',
                'alpha' => '([A-Za-z]+)',
                'alnum' => '([A-Za-z0-9]+)',
                'slug' => '([a-zA-Z-_]+)',
                '*' => '(.*)',
            ];

            if (\array_key_exists($token, $map)) {
                return $map[$token];
            }

            // enum pattern "option1,option2"
            if (preg_match('~"([^"]+)"~', $token, $match)) {
                $options = implode('|', explode(',', $match[1]));
                return '('.$options.')';
            }

            // default is to match anything
            return '([^/]+)';
        };

        foreach ($matches[0] as $index => $search) {
            $path = str_replace($search, $pattern($matches[3][$index]), $path);
        }

        return $path;
    }
}
