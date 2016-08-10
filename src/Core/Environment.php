<?php

namespace Kraken\Core;

use Kraken\Config\ConfigInterface;
use Kraken\Runtime\Runtime;
use Kraken\Util\Invoker\Invoker;
use Kraken\Util\Invoker\InvokerInterface;

class Environment implements EnvironmentInterface
{
    /**
     * @var int
     */
    const SIGTERM = 15;

    /**
     * @var InvokerInterface
     */
    protected $invoker;

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
        $this->invoker = $this->createInvoker();
        $this->context = $context;
        $this->config = $config;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->invoker);
        unset($this->context);
        unset($this->config);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setOption($key, $val)
    {
        return $this->invoker->call('ini_set', [ $key, $val ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getOption($key)
    {
        return $this->invoker->call('ini_get', [ $key ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function restoreOption($key)
    {
        $this->invoker->call('ini_restore', [ $key ]);

        return $this->getOption($key);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getEnv($key)
    {
        return $this->config->get('core.' . $key);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function matchEnv($key, $val)
    {
        return $this->getEnv($key) === $val;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerErrorHandler(callable $handler)
    {
        $this->invoker->call('set_error_handler', [ $handler ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerShutdownHandler(callable $handler)
    {
        $this->invoker->call('register_shutdown_function', [
            function() use($handler) {
                return $handler(
                    $this->context->type() === Runtime::UNIT_PROCESS
                );
            }
        ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerExceptionHandler(callable $handler)
    {
        $this->invoker->call('set_exception_handler', [ $handler ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerTerminationHandler(callable $handler)
    {
        $this->invoker->call('pcntl_signal', [ self::SIGTERM, $handler ]);
    }

    /**
     * @return InvokerInterface
     */
    protected function createInvoker()
    {
        return new Invoker();
    }
}
