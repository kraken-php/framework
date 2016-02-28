<?php

namespace Kraken\Supervisor;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class SupervisorPlugin implements SupervisorPluginInterface
{
    /**
     * @var bool
     */
    protected $registered;

    /**
     * @param SupervisorInterface $supervisor
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $supervisor)
    {
        try
        {
            $this->register($supervisor);
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
     * @param SupervisorInterface $supervisor
     */
    public function unregisterPlugin(SupervisorInterface $supervisor)
    {
        $this->unregister($supervisor);
        $this->registered = false;
    }

    /**
     * @param SupervisorInterface $supervisor
     */
    protected function register(SupervisorInterface $supervisor)
    {}

    /**
     * @param SupervisorInterface $supervisor
     */
    protected function unregister(SupervisorInterface $supervisor)
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
