<?php

namespace Kraken\Supervisor;

use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface SupervisorPluginInterface
{
    /**
     * @param SupervisorInterface $supervisor
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $supervisor);

    /**
     * @param SupervisorInterface $supervisor
     */
    public function unregisterPlugin(SupervisorInterface $supervisor);
}
