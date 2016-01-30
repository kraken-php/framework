<?php

namespace Kraken\Error;

use Exception;
use Kraken\Exception\Runtime\ExecutionException;

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
     * @param Exception $ex
     * @throws ExecutionException
     */
    protected function exception(Exception $ex)
    {
        throw new ExecutionException("ServiceProvider [" . get_class($this) . "] raised an error.", $ex);
    }
}
