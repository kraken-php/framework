<?php

namespace Kraken\Event;

use Kraken\Loop\LoopAwareInterface;
use Kraken\Loop\LoopInterface;

class AsyncEventEmitter implements EventEmitterInterface, LoopAwareInterface
{
    use AsyncEventEmitterTrait;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->setLoop($loop);
    }
}
