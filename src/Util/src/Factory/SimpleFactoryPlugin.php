<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

abstract class SimpleFactoryPlugin implements SimpleFactoryPluginInterface
{
    /**
     * @var bool
     */
    private $registered;

    /**
     * @override
     * @inheritDoc
     */
    public function registerPlugin(SimpleFactoryInterface $factory)
    {
        try
        {
            $this->register($factory);
            $this->registered = true;
        }
        catch (Error $ex)
        {
            $this->throwException($ex);
        }
        catch (Exception $ex)
        {
            $this->throwException($ex);
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unregisterPlugin(SimpleFactoryInterface $factory)
    {
        if ($this->registered)
        {
            $this->unregister($factory);
            $this->registered = false;
        }

        return $this;
    }

    /**
     * Define how plugin should be registered.
     *
     * @param SimpleFactoryInterface $factory
     */
    protected function register(SimpleFactoryInterface $factory)
    {}

    /**
     * Define how plugin should be unregistered.
     *
     * @param SimpleFactoryInterface $factory
     */
    protected function unregister(SimpleFactoryInterface $factory)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    private function throwException($ex)
    {
        throw new ExecutionException("SimpleFactoryPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
