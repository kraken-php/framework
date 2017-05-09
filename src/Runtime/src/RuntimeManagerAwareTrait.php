<?php

namespace Kraken\Runtime;

trait RuntimeManagerAwareTrait
{
    /**
     * @var RuntimeManagerInterface|null
     */
    protected $runtimeManager = null;

    /**
     * @see RuntimeManagerAwareInterface::setRuntimeManager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null)
    {
        $this->runtimeManager = $manager;
    }

    /**
     * @see RuntimeManagerAwareInterface::getRuntimeManager
     */
    public function getRuntimeManager()
    {
        return $this->runtimeManager;
    }
}
