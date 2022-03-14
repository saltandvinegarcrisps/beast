<?php

namespace Beast\Framework\Config;

abstract class AbstractFileLoader implements FileLoaderInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $extension;

    public function __construct(string $path)
    {
        $path = realpath($path);

        if (false === $path) {
            throw new ConfigException(
                'Config dir not found: '.$path
            );
        }

        $this->path = $path;
    }

    /**
     * Get the path of a config file
     *
     * @param string $name
     * @return null|string
     */
    protected function filepath(string $name): ?string
    {
        $filepath = $this->path . '/' . $name . $this->extension;

        if (!is_file($filepath)) {
            return null;
        }

        return $filepath;
    }

    /**
     * Load file contents
     *
     * @param string $file
     * @return array<mixed>|null
     */
    abstract protected function load(string $file): ?array;

    /**
     * Split key into parts, the first part will always be the file name
     *
     * @param string $name
     * @return array<int, array<int, int|string>|string>
     */
    protected function parts(string $name): array
    {
        if (\strlen($name) === 0) {
            throw new ConfigException(
                'Parameter name cannot be empty'
            );
        }

        $keys = explode('.', $name);

        $file = array_shift($keys);

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

        if (!\is_string($file)) {
            throw new \UnexpectedValueException(sprintf('Expected `$file` to be a string, got %s', \gettype($file)));
        }

        if (!\is_array($keys)) {
            throw new \UnexpectedValueException(sprintf('Expected `$keys` to be a array, got %s', \gettype($keys)));
        }

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

        if (!\is_string($file)) {
            throw new \UnexpectedValueException(sprintf('Expected `$file` to be a string, got %s', \gettype($file)));
        }

        if (!\is_array($keys)) {
            throw new \UnexpectedValueException(sprintf('Expected `$keys` to be a array, got %s', \gettype($keys)));
        }

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
