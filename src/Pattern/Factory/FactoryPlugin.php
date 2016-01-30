<?php

namespace Kraken\Pattern\Factory;

use Exception;
use Kraken\Exception\Runtime\ExecutionException;

class FactoryPlugin implements FactoryPluginInterface
{
    /**
     * @var bool
     */
    protected $registered;

    /**
     * @param FactoryInterface $factory
     * @throws ExecutionException
     */
    public function registerPlugin(FactoryInterface $factory)
    {
        try
        {
            $this->register($factory);
            $this->registered = true;
        }
        catch (Exception $ex)
        {
            $this->exception($ex);
        }
    }

    /**
     * @param FactoryInterface $factory
     */
    public function unregisterPlugin(FactoryInterface $factory)
    {
        $this->unregister($factory);
        $this->registered = false;
    }

    /**
     * @param FactoryInterface $factory
     */
    protected function register(FactoryInterface $factory)
    {}

    /**
     * @param FactoryInterface $factory
     */
    protected function unregister(FactoryInterface $factory)
    {}

    /**
     * @param Exception $ex
     * @throws ExecutionException
     */
    protected function exception(Exception $ex)
    {
        throw new ExecutionException("FactoryPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
