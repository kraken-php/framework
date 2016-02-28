<?php

namespace Kraken\Supervisor;

interface SupervisorAwareInterface
{
    /**
     * @param SupervisorInterface $supervisor
     */
    public function setSupervisor(SupervisorInterface $supervisor);

    /**
     * @return SupervisorInterface
     */
    public function getSupervisor();

    /**
     * @return SupervisorInterface
     */
    public function supervisor();
}
