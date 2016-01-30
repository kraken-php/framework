<?php

namespace Kraken\Config;

interface ConfigInterface
{
    /**
     * @param array $config
     */
    public function setConfig($config);

    /**
     * @param callable|null $handler
     */
    public function setOverwriteHandler(callable $handler = null);

    /**
     * @param array $config
     * @return ConfigInterface
     */
    public function merge($config);

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = '', $default = null);

    /**
     * @param string $key
     * @return bool
     */
    public function remove($key);

    /**
     * @return array
     */
    public function all();
}
