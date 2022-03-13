<?php

namespace Beast\Framework\Config;

interface FileLoaderInterface
{
    /**
     * Key exists in config file
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Find key from config file
     *
     * @param string $name
     * @param null|mixed $default
     * @return null|mixed
     */
    public function get(string $name, $default = null);
}
