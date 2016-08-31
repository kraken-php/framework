<?php

namespace Kraken\Environment;

use Kraken\Core\CoreInputContextInterface;
use Kraken\Environment\Loader\Loader;
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
     * @var Loader
     */
    protected $loader;

    /**
     * @param CoreInputContextInterface $context
     * @param string $filePath
     */
    public function __construct(CoreInputContextInterface $context, $filePath = '')
    {
        $this->invoker = $this->createInvoker();
        $this->context = $context;
        $this->loader  = $this->createLoader($filePath);

        if ($filePath !== '')
        {
            $this->loader->load();
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->invoker);
        unset($this->context);
        unset($this->loader);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setEnv($name, $value)
    {
        $this->loader->setEnvironmentVariable($name, $value);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getEnv($name)
    {
        return $this->loader->getEnvironmentVariable($name);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeEnv($name)
    {
        $this->loader->clearEnvironmentVariable($name);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setOption($key, $val)
    {
        $this->invoker->call('ini_set', [ $key, $val ]);
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
    public function removeOption($key)
    {
        $this->invoker->call('ini_restore', [ $key ]);
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
                    $this->context->getType() === Runtime::UNIT_PROCESS
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

    /**
     * @param string $filePath
     * @return Loader
     */
    protected function createLoader($filePath)
    {
        return new Loader($filePath, false);
    }
}
