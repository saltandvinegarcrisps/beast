<?php

namespace Beast\Framework\Config;

interface FileLoaderInterface
{
    /**
     * Key exists in config file
     *
     * @param string
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Find key from config file
     *
     * @param string
     * @param mixed
     * @return mixed
     */
    public function get(string $name, $default = null);
}
