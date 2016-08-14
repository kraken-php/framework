<?php

namespace Kraken\Loop;

interface LoopGetterAwareInterface
{
    /**
     * Return the loop of which object is aware of or null if none was set.
     *
     * @return LoopInterface|null
     */
    public function getLoop();
}
