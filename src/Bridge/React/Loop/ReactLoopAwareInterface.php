<?php

namespace Kraken\Bridge\React\Loop;

use React\EventLoop\LoopInterface;

interface ReactLoopAwareInterface
{
    /**
     * Returns instance of original React loop
     *
     * @return LoopInterface
     */
    public function getReactLoop();
}
