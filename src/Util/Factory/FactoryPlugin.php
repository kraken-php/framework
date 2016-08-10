<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

abstract class FactoryPlugin implements FactoryPluginInterface
{
    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @override
     * @inheritDoc
     */
    public function registerPlugin(FactoryInterface $factory)
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
    public function unregisterPlugin(FactoryInterface $factory)
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
     * @param FactoryInterface $factory
     */
    protected function register(FactoryInterface $factory)
    {}

    /**
     * Define how plugin should be unregistered.
     *
     * @param FactoryInterface $factory
     */
    protected function unregister(FactoryInterface $factory)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    private function throwException($ex)
    {
        throw new ExecutionException("FactoryPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
