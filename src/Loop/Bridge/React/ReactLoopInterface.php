<?php

namespace Kraken\Loop\Bridge\React;

use Kraken\Loop\LoopInterface;

interface ReactLoopInterface extends \React\EventLoop\LoopInterface
{
    /**
     * Return the actual LoopInterface which is adapted by current ReactLoopInterface.
     *
     * @return LoopInterface
     */
    public function getActualLoop();
}
