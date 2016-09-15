<?php

namespace Kraken\Supervision;

interface SupervisorAwareInterface
{
    /**
     * Set Supervisor of which object is aware of or delete it by setting it to null.
     *
     * @param SupervisorInterface|null $supervisor
     */
    public function setSupervisor(SupervisorInterface $supervisor = null);

    /**
     * Get Supervisor of which object is aware of or null if no object is set.
     *
     * @return SupervisorInterface|null
     */
    public function getSupervisor();
}
