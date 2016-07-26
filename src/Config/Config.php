<?php

namespace Kraken\Config;

use Kraken\Support\ArraySupport;

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
        $this->setConfig($config);
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
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param callable|null $handler
     */
    public function setOverwriteHandler(callable $handler = null)
    {
        $this->overwriteHandler = ($handler !== null) ? $handler : function($current, $new) {
            return $this->getOverwriteHandlerMerger($current, $new);
        };
    }

    /**
     * @param array $config
     * @return ConfigInterface
     */
    public function merge($config)
    {
        $this->config = $this->overwrite($this->config, $config);

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return ArraySupport::exists($this->config, $key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function set($key, $value)
    {
        return ArraySupport::set($this->config, $key, $value);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = '', $default = null)
    {
        if ($key === '')
        {
            return $this->all();
        }

        return ArraySupport::get($this->config, $key, $default);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        return ArraySupport::remove($this->config, $key);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * @param array $current
     * @param array $new
     * @return callable
     */
    public function getOverwriteHandlerMerger($current, $new)
    {
        return ArraySupport::merge([ $current, $new ]);
    }

    /**
     * @param array $current
     * @param array $new
     * @return callable
     */
    public function getOverwriteHandlerReplacer($current, $new)
    {
        return ArraySupport::replace([ $current, $new ]);
    }

    /**
     * @param array $current
     * @param array $new
     * @return callable
     */
    public function getOverwriteHandlerIsolater($current, $new)
    {
        return $new;
    }

    /**
     * Overwrites current config using known method.
     *
     * @param array $current
     * @param array $new
     * @return array
     */
    protected function overwrite($current, $new)
    {
        return call_user_func_array($this->overwriteHandler, [ $current, $new ]);
    }
}
