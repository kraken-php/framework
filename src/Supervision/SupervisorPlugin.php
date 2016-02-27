<?php

namespace Kraken\Supervision;

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
     * @param SupervisorInterface $manager
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $manager)
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
     * @param SupervisorInterface $manager
     */
    public function unregisterPlugin(SupervisorInterface $manager)
    {
        $this->unregister($manager);
        $this->registered = false;
    }

    /**
     * @param SupervisorInterface $manager
     */
    protected function register(SupervisorInterface $manager)
    {}

    /**
     * @param SupervisorInterface $manager
     */
    protected function unregister(SupervisorInterface $manager)
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
