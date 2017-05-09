<?php

namespace Kraken\Loop;

interface LoopExtendedAwareInterface
{
    /**
     * Set the loop of which object is aware of.
     *
     * @param LoopExtendedInterface|null $loop
     */
    public function setLoop(LoopExtendedInterface $loop = null);

    /**
     * Return the loop of which object is aware of.
     *
     * @return LoopExtendedInterface|null
     */
    public function getLoop();
}
