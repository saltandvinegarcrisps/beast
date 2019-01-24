<?php

namespace Beast\Framework\Support;

class Config
{
    protected $path;

    public function __construct(string $path)
    {
        if (!\is_dir($path)) {
            throw new ConfigException(
                'Config dir not found: '.$path
            );
        }

        $this->path = $path;
    }

    /**
     * Get the path of a json file
     *
     * @param string
     * @return string
     */
    protected function filepath(string $name): string
    {
        $filepath = $this->path . '/' . $name . '.json';

        if (!\is_file($filepath)) {
            throw new ConfigException(
                'Config file not found: '.$filepath
            );
        }

        return $filepath;
    }

    /**
     * Decode the json file
     *
     * @param string
     * @return array
     */
    protected function load(string $path): array
    {
        $jsonStr = \file_get_contents($path);

        if (false === $jsonStr) {
            throw new ConfigException(
                'Failed to read file: '.$path
            );
        }

        $data = \json_decode($jsonStr, true);

        if (null === $data) {
            throw new ConfigException(
                'json_decode error in '.$path.': ' . \json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * Split key into parts, the first part will always be the file name
     *
     * @param string
     * @return array
     */
    protected function parts(string $name): array
    {
        if (\strlen($name) === 0) {
            throw new ConfigException(
                'Parameter name cannot be empty'
            );
        }

        $keys = \explode('.', $name);

        if (empty($keys)) {
            throw new \ConfigException(
                'Failed to extract keys from parameter name'
            );
        }

        $file = \array_shift($keys);

        if (null === $file) {
            throw new ConfigException(
                'Failed to shift first key from keys'
            );
        }

        return [$file, $keys];
    }

    /**
     * Fetch a config value from a json file
     *
     * @param string
     * @param mixed
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        $value = $this->find($name, $default);

        // replace ENV variables
        if (\is_string($value) && \strpos($value, '$') === 0) {
            $key = \substr($value, 2, -1);
            $value = \getenv($key) ?: $default;
        }

        return $value;
    }

    /**
     * Find key from json file
     *
     * @param string
     * @param mixed
     * @return mixed
     */
    protected function find(string $name, $default = null)
    {
        [$file, $keys] = $this->parts($name);

        $path = $this->filepath($file);

        $config = $this->load($path);

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $config)) {
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }

    /**
     * Put a value into a json config file
     *
     * @param string
     * @param mixed
     * @return bool
     */
    public function put(string $name, $value): bool
    {
        [$file, $keys] = $this->parts($name);

        $path = $this->filepath($file);

        $original = $this->load($path);
        $config =& $original;

        while (\count($keys) > 1) {
            $key = \array_shift($keys);

            if (!\array_key_exists($key, $config)) {
                $config[$key] = [];
            }

            $config =& $config[$key];
        }

        $config[\array_shift($keys)] = $value;

        return $this->save($path, $original);
    }

    /**
     * Save json config into a file
     *
     * @param string
     * @param array
     * @return bool
     */
    protected function save(string $path, array $config): bool
    {
        $json = \json_encode($config, JSON_PRETTY_PRINT);
        return false !== \file_put_contents($path, $json, LOCK_EX);
    }
}
