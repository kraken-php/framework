<?php

namespace Kraken\Supervision;

trait SupervisorAwareTrait
{
    /**
     * @var SupervisorInterface|null
     */
    protected $supervisor = null;

    /**
     * @see SupervisorAwareInterface::setSupervisor
     */
    public function setSupervisor(SupervisorInterface $supervisor = null)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @see SupervisorAwareInterface::getSupervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }
}
