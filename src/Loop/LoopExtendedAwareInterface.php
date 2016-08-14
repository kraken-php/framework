<?php

namespace Kraken\Loop;

interface LoopExtendedAwareInterface
{
    /**
     * Set the loop of which object is aware of.
     *
     * @param LoopExtendedInterface $loop
     */
    public function setLoop(LoopExtendedInterface $loop);

    /**
     * Return the loop of which object is aware of.
     *
     * @return LoopExtendedInterface
     */
    public function getLoop();
}
