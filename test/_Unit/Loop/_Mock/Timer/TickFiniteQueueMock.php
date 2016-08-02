<?php

namespace Kraken\_Unit\Loop\_Mock\Timer;

use Kraken\Loop\Tick\TickFiniteQueue;
use SplQueue;

class TickFiniteQueueMock extends TickFiniteQueue
{
    /**
     * @return SplQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
