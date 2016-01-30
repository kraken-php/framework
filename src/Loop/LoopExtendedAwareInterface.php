<?php

namespace Kraken\Loop;

interface LoopExtendedAwareInterface
{
    /**
     * @param LoopExtendedInterface $loop
     */
    public function setLoop(LoopExtendedInterface $loop);

    /**
     * @return LoopExtendedInterface
     */
    public function getLoop();

    /**
     * @return LoopExtendedInterface
     */
    public function loop();
}
