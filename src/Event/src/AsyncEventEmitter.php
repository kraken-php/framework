<?php

namespace Kraken\Event;

use Dazzle\Loop\LoopAwareInterface;
use Dazzle\Loop\LoopInterface;

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
