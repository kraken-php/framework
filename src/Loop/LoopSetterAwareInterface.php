<?php

namespace Kraken\Loop;

interface LoopSetterAwareInterface
{
    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop);
}
