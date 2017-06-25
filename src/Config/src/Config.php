<?php

namespace Kraken\Config;

use Kraken\Config\Overwrite\OverwriteMerger;
use Dazzle\Util\Support\ArraySupport;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var callable
     */
    protected $overwriteHandler;

    /**
     * @param array $config
     * @param callable|null $handler
     */
    public function __construct($config = [], callable $handler = null)
    {
        $this->setConfiguration($config);
        $this->setOverwriteHandler($handler);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->config);
        unset($this->overwriteHandler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setConfiguration($config)
    {
        $this->config = ArraySupport::expand($config);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setOverwriteHandler(callable $handler = null)
    {
        $this->overwriteHandler = $handler !== null ? $handler : $this->getDefaultHandler();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getOverwriteHandler()
    {
        return $this->overwriteHandler;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function merge($config, $handler = null)
    {
        $this->config = $this->overwrite($this->config, $config, $handler);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function exists($key)
    {
        return ArraySupport::exists($this->config, $key);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function set($key, $value)
    {
        return ArraySupport::set($this->config, $key, $value);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function get($key = '', $default = null)
    {
        return $key === '' ? $this->getAll() : ArraySupport::get($this->config, $key, $default);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function remove($key)
    {
        return ArraySupport::remove($this->config, $key);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Overwrites current config using known method.
     *
     * @param array $current
     * @param array $new
     * @param callable|null $handler
     * @return array
     */
    protected function overwrite($current, $new, $handler = null)
    {
        if ($handler === null)
        {
            $handler = $this->overwriteHandler;
        }

        return $handler($current, $new);
    }

    /**
     * @return callable
     */
    protected function getDefaultHandler()
    {
        return new OverwriteMerger();
    }
}
