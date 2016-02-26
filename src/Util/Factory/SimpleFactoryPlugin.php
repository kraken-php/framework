<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class SimpleFactoryPlugin implements SimpleFactoryPluginInterface
{
    /**
     * @var bool
     */
    protected $registered;

    /**
     * @param SimpleFactoryInterface $factory
     * @throws ExecutionException
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
            $this->exception($ex);
        }
        catch (Exception $ex)
        {
            $this->exception($ex);
        }
    }

    /**
     * @param SimpleFactoryInterface $factory
     */
    public function unregisterPlugin(SimpleFactoryInterface $factory)
    {
        $this->unregister($factory);
        $this->registered = false;
    }

    /**
     * @param SimpleFactoryInterface $factory
     */
    protected function register(SimpleFactoryInterface $factory)
    {}

    /**
     * @param SimpleFactoryInterface $factory
     */
    protected function unregister(SimpleFactoryInterface $factory)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    protected function exception($ex)
    {
        throw new ExecutionException("SimpleFactoryPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
