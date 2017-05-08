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
    protected $registered = false;

    /**
     * @override
     * @inheritDoc
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
    public function unregisterPlugin(SupervisorInterface $supervisor)
    {
        if ($this->registered)
        {
            $this->unregister($supervisor);
            $this->registered = false;
        }

        return $this;
    }

    /**
     * Define how plugin should be registered.
     *
     * @param SupervisorInterface $supervisor
     */
    protected function register(SupervisorInterface $supervisor)
    {}

    /**
     * Define how plugin should be unregistered.
     *
     * @param SupervisorInterface $supervisor
     */
    protected function unregister(SupervisorInterface $supervisor)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    private function throwException($ex)
    {
        throw new ExecutionException("SupervisorPlugin [" . get_class($this) . "] raised an error.", $ex);
    }
}
