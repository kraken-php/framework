<?php

namespace Kraken\Redis\Dispatcher;

use Kraken\Event\EventEmitter;
use Kraken\Event\EventEmitterInterface;
use Kraken\Promise\Deferred;

abstract class Dispatcher extends EventEmitter implements DispatcherInterface,EventEmitterInterface
{
    private $loop;
    /**
     * Dispatcher constructor.
     */
    public function __construct($loop)
    {
        $this->loop = $loop;
        parent::__construct($loop);
    }

    public function getLoop()
    {
        return $this->loop;
    }
}