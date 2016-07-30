<?php

namespace Kraken\Config;

interface ConfigReaderInterface
{
    /**
     * Check if configuration under $key exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Get configuration saved under $key or $default if it does not exist.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = '', $default = null);

    /**
     * Get all configuration options.
     *
     * @return array
     */
    public function getAll();
}
