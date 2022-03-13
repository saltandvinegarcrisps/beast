<?php

namespace Beast\Framework\Config;

class JsonFileLoader extends AbstractFileLoader
{
    protected $extension = '.json';

    /**
     * {@inherit}
     */
    protected function load(string $file): ?array
    {
        $path = $this->filepath($file);

        if (null === $path) {
            return null;
        }

        $jsonStr = file_get_contents($path);

        if (false === $jsonStr) {
            throw new ConfigException(
                'Failed to read file: '.$path
            );
        }

        $json = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($json)) {
            throw new ConfigException(
                'JSON config did not return an array: ' . $path
            );
        }

        return $json;
    }
}
