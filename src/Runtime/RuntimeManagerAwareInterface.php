<?php

namespace Kraken\Runtime;

interface RuntimeManagerAwareInterface
{
    /**
     * @param RuntimeManagerInterface|null $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager = null);

    /**
     * @return RuntimeManagerInterface|null
     */
    public function getRuntimeManager();
}
