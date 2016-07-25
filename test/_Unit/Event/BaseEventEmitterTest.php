<?php

namespace Kraken\_Unit\Event;

use Kraken\Event\BaseEventEmitter;
use Kraken\Event\EventEmitterInterface;

class BaseEventEmitterTest extends EventEmitterTest
{
    /**
     * @return EventEmitterInterface[][]
     */
    public function emitterProvider()
    {
        return [
            [ new BaseEventEmitter(parent::createLoopMock()) ]
        ];
    }
}
