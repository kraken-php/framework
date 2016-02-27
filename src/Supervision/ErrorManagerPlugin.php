<?php

namespace Kraken\Supervision;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class ErrorManagerPlugin implements ErrorManagerPluginInterface
{
    /**
     * @var bool
     */
    protected $registered;

    /**
     * @param ErrorManagerInterface $manager
     * @throws ExecutionException
     */
    public function registerPlugin(ErrorManagerInterface $manager)
    {
        try
        {
            $this->register($manager);
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
     * @param ErrorManagerInterface $manager
     */
    public function unregisterPlugin(ErrorManagerInterface $manager)
    {
        $this->unregister($manager);
        $this->registered = false;
    }

    /**
     * @param ErrorManagerInterface $manager
     */
    protected function register(ErrorManagerInterface $manager)
    {}

    /**
     * @param ErrorManagerInterface $manager
     */
    protected function unregister(ErrorManagerInterface $manager)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    protected function exception($ex)
    {
        throw new ExecutionException("ServiceProvider [" . get_class($this) . "] raised an error.", $ex);
    }
}
