<?php

namespace Beast\Framework\Config;

class PhpFileLoader extends AbstractFileLoader
{
    /**
     * @var string
     */
    protected $extension = '.php';

    /**
     * {@inherit}
     */
    protected function load(string $file): ?array
    {
        $path = $this->filepath($file);

        if (null === $path) {
            return null;
        }

        return require $path;
    }
}
