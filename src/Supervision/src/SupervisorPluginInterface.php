<?php

namespace Kraken\Supervision;

use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface SupervisorPluginInterface
{
    /**
     * Register plugin to Supervisor.
     *
     * @param SupervisorInterface $supervisor
     * @return SupervisorPluginInterface
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $supervisor);

    /**
     * Unregister plugin from Supervisor.
     *
     * @param SupervisorInterface $supervisor
     * @return SupervisorPluginInterface
     */
    public function unregisterPlugin(SupervisorInterface $supervisor);
}
