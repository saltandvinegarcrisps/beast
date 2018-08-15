<?php

namespace Beast\Framework\Support;

class Config
{
    protected $path;

    public function __construct(string $path)
    {
        if (! is_dir($path)) {
            throw new \InvalidArgumentException(
                'Config path not found: '.$path
            );
        }

        $this->path = $path;
    }

    protected function filepath(string $name): string
    {
        $filepath = $this->path . '/' . $name . '.json';

        if (! is_file($filepath)) {
            throw new \InvalidArgumentException(
                'Config file not found: '.$filepath
            );
        }

        return $filepath;
    }

    protected function load(string $path): array
    {
        $json = file_get_contents($path);

        if (false === $json) {
            throw new \InvalidArgumentException(
                'Failed to read file: '.$path
            );
        }

        $data = json_decode($json, true);

        if (null === $data) {
            throw new \InvalidArgumentException(
                'json_decode error in '.$path.': ' . json_last_error_msg()
            );
        }

        return $data;
    }

    protected function save(string $path, array $config): bool
    {
        $json = json_encode($config, JSON_PRETTY_PRINT);
        return false !== file_put_contents($path, $json, LOCK_EX);
    }

    protected function parts(string $name): array
    {
        if (strlen($name) === 0) {
            throw new \InvalidArgumentException(
                'Parameter name cannot be empty'
            );
        }

        $keys = explode('.', $name);

        if (empty($keys)) {
            throw new \InvalidArgumentException(
                'Failed to extract keys from parameter name'
            );
        }

        $file = array_shift($keys);

        if (null === $file) {
            throw new \InvalidArgumentException(
                'Failed to shift first key from parameter name'
            );
        }

        return [$file, $keys];
    }

    public function get(string $name, $default = null)
    {
        [$file, $keys] = $this->parts($name);

        $path = $this->filepath($file);

        $config = $this->load($path);

        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }

    public function put(string $name, $value): bool
    {
        [$file, $keys] = $this->parts($name);

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
