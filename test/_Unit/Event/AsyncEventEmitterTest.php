<?php

namespace Kraken\_Unit\Event;

use Kraken\Event\AsyncEventEmitter;
use Kraken\Event\EventEmitterInterface;

class AsyncEventEmitterTest extends EventEmitterTest
{
    /**
     * @return EventEmitterInterface[][]
     */
    public function emitterProvider()
    {
        return [
            [ new AsyncEventEmitter(parent::createLoopMock()) ]
        ];
    }
}
