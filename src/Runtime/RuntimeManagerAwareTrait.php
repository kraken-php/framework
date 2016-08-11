<?php

namespace Kraken\Runtime;

trait RuntimeManagerAwareTrait
{
    /**
     * @var RuntimeManagerInterface|null
     */
    protected $runtimeManager = null;

    /**
     * @param RuntimeManagerInterface|null $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null)
    {
        $this->runtimeManager = $manager;
    }

    /**
     * @return RuntimeManagerInterface|null
     */
    public function getRuntimeManager()
    {
        return $this->runtimeManager;
    }
}
