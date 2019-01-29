<?php

namespace Beast\Framework\Config;

abstract class AbstractFileLoader implements FileLoaderInterface
{
    protected $path;

    protected $extension;

    public function __construct(string $path)
    {
        $this->path = \realpath($path);

        if (false === $this->path) {
            throw new ConfigException(
                'Config dir not found: '.$path
            );
        }
    }

    /**
     * Get the path of a config file
     *
     * @param string
     * @return null|string
     */
    protected function filepath(string $name): ?string
    {
        $filepath = $this->path . '/' . $name . $this->extension;

        if (!\is_file($filepath)) {
            return null;
        }

        return $filepath;
    }

    /**
     * Load file contents
     *
     * @param string
     * @return null|array
     */
    abstract protected function load(string $file): ?array;

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
            throw new ConfigException(
                'Failed to extract keys from parameter name'
            );
        }

        $file = \array_shift($keys);

        if (!$file) {
            throw new ConfigException(
                'Failed to shift first key from keys'
            );
        }

        return [$file, $keys];
    }

    /**
     * {@inherit}
     */
    public function has(string $name): bool
    {
        [$file, $keys] = $this->parts($name);

        $config = $this->load($file);

        if (null === $config) {
            return false;
        }

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $config)) {
                return false;
            }
            $config = $config[$key];
        }

        return true;
    }

    /**
     * {@inherit}
     */
    public function get(string $name, $default = null)
    {
        [$file, $keys] = $this->parts($name);

        $config = $this->load($file);

        if (null === $config) {
            return $default;
        }

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $config)) {
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }
}
