<?php

namespace Kraken\_Unit\Loop\_Mock\Timer;

use Kraken\Loop\Tick\TickContinousQueue;
use SplQueue;

class TickContinousQueueMock extends TickContinousQueue
{
    /**
     * @return SplQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
