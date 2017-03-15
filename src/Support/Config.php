<?php

namespace Beast\Framework\Support;

class Config
{
    protected $path;

    public function __construct(string $path)
    {
        if (! is_dir($path)) {
            throw new \InvalidArgumentException(sprintf('config dir "%s" not found', $path));
        }

        $this->path = $path;
    }

    protected function filepath(string $name): string
    {
        $path = $this->path . '/' . $name . '.json';

        if (! is_file($path)) {
            throw new \InvalidArgumentException(sprintf('config file "%s" not found', $path));
        }

        return $path;
    }

    protected function load(string $path): array
    {
        $jsonStr = file_get_contents($path);

        $data = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Error in json file ' . $path . ': ' . json_last_error_msg());
        }

        return $data;
    }

    protected function save(string $path, array $config): bool
    {
        $json = json_encode($config, JSON_PRETTY_PRINT);
        return false !== file_put_contents($path, $json, LOCK_EX);
    }

    public function get(string $name)
    {
        $keys = explode('.', $name);

        $file = array_shift($keys);

        $path = $this->filepath($file);

        $config = $this->load($path);

        foreach ($keys as $key) {
            if (array_key_exists($key, $config)) {
                $config = $config[$key];
            }
        }

        return $config;
    }

    public function put(string $name, $value): bool
    {
        $keys = explode('.', $name);

        $file = array_shift($keys);

        $path = $this->filepath($file);

        $original = $this->load($path);
        $config =& $original;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (! array_key_exists($key, $config)) {
                $config[$key] = [];
            }

            $config =& $config[$key];
        }

        $config[array_shift($keys)] = $value;

        return $this->save($path, $original);
    }
}
