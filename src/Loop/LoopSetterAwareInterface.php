<?php

namespace Kraken\Loop;

interface LoopSetterAwareInterface
{
    /**
     * Set the loop of which object is aware of or delete is setting to null.
     *
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop = null);
}
