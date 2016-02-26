<?php

namespace Kraken\Core;

use Kraken\Config\ConfigInterface;
use Kraken\Runtime\Runtime;

class Environment implements EnvironmentInterface
{
    /**
     * @var CoreInputContextInterface
     */
    protected $context;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param CoreInputContextInterface $context
     * @param ConfigInterface $config
     */
    public function __construct(CoreInputContextInterface $context, ConfigInterface $config)
    {
        $this->context = $context;
        $this->config = $config;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->context);
        unset($this->config);
    }

    /**
     * @param string $key
     * @param string $value
     * @return string|bool
     */
    public function setOption($key, $value)
    {
        return ini_set($key, $value);
    }

    /**
     * @param string $key
     * @return string|bool
     */
    public function getOption($key)
    {
        return ini_get($key);
    }

    /**
     * @param string $key
     * @return string|bool
     */
    public function restoreOption($key)
    {
        ini_restore($key);

        return $this->getOption($key);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getEnv($key)
    {
        return $this->config->get('core.' . $key);
    }

    /**
     * @param string $key
     * @param string $val
     * @return bool
     */
    public function matchEnv($key, $val)
    {
        return $this->getEnv($key) === $val;
    }

    /**
     * @param callable $handler
     */
    public function registerErrorHandler(callable $handler)
    {
        set_error_handler($handler);
    }

    /**
     * @param callable $handler
     */
    public function registerShutdownHandler(callable $handler)
    {
        register_shutdown_function(function() use($handler) {
            return $handler(
                $this->context->type() === Runtime::UNIT_PROCESS
            );
        });
    }

    /**
     * @param callable $handler
     */
    public function registerExceptionHandler(callable $handler)
    {
        set_exception_handler($handler);
    }
}
