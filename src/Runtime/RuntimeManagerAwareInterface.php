<?php

namespace Kraken\Runtime;

interface RuntimeManagerAwareInterface
{
    /**
     * Set RuntimeManager of which object is aware of or null to remove it.
     *
     * @param RuntimeManagerInterface|null $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null);

    /**
     * Get RuntimeManager of which object is aware of or null if it does not exist.
     *
     * @return RuntimeManagerInterface|null
     */
    public function getRuntimeManager();
}
