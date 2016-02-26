<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

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
        catch (Error $ex)
        {
            $this->exception($ex);
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
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    protected function exception($ex)
    {
        throw new ExecutionException("FactoryPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
