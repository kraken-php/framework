<?php

namespace Kraken\Supervisor;

use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface SupervisorPluginInterface
{
    /**
     * Register plugin to Supevisor.
     *
     * @param SupervisorInterface $supervisor
     * @return SupervisorPluginInterface
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $supervisor);

    /**
     * Unregister plugin from Supevisor.
     *
     * @param SupervisorInterface $supervisor
     * @return SupervisorPluginInterface
     */
    public function unregisterPlugin(SupervisorInterface $supervisor);
}
