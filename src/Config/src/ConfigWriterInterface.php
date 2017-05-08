<?php

namespace Kraken\Config;

interface ConfigWriterInterface
{
    /**
     * Merge given configuration with existing one using previously set overwrite handler.
     *
     * @param array $config
     * @param callable|null $handler
     * @return ConfigInterface
     */
    public function merge($config, $handler = null);

    /**
     * Set configuration under $key to $value.
     *
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function set($key, $value);

    /**
     * Remove configuration saved under $key or do nothing if it does not exist.
     *
     * @param string $key
     * @return bool
     */
    public function remove($key);
}
