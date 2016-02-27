<?php

namespace Kraken\Supervision;

interface SupervisorAwareInterface
{
    /**
     * @param SupervisorInterface $manager
     */
    public function setSupervisor(SupervisorInterface $manager);

    /**
     * @return SupervisorInterface
     */
    public function getSupervisor();

    /**
     * @return SupervisorInterface
     */
    public function supervisor();
}
