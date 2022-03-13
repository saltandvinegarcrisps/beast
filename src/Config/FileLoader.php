<?php

namespace Beast\Framework\Config;

class FileLoader implements FileLoaderInterface
{
    protected $loaders;

    public function __construct(string $path, array $loaders = [])
    {
        $this->loaders = $loaders;
        if (empty($this->loaders)) {
            $this->loaders[] = new JsonFileLoader($path);
            $this->loaders[] = new PhpFileLoader($path);
        }
    }

    /**
     * {@inherit}
     */
    public function has(string $name): bool
    {
        $reduce = function (bool $carry, FileLoaderInterface $loader) use ($name) {
            return $loader->has($name) ? true : $carry;
        };
        return array_reduce($this->loaders, $reduce, false);
    }

    /**
     * {@inherit}
     */
    public function get(string $name, $default = null)
    {
        $reduce = function ($carry, FileLoaderInterface $loader) use ($name) {
            return $loader->has($name) ? $loader->get($name) : $carry;
        };
        return array_reduce($this->loaders, $reduce, $default);
    }
}
