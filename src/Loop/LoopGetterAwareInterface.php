<?php

namespace Kraken\Loop;

interface LoopGetterAwareInterface
{
    /**
     * @return LoopInterface
     */
    public function getLoop();

    /**
     * @return LoopInterface
     */
    public function loop();
}
