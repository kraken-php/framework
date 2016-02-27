<?php

namespace Kraken\Supervision;

use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface SupervisorPluginInterface
{
    /**
     * @param SupervisorInterface $manager
     * @throws ExecutionException
     */
    public function registerPlugin(SupervisorInterface $manager);

    /**
     * @param SupervisorInterface $manager
     */
    public function unregisterPlugin(SupervisorInterface $manager);
}
