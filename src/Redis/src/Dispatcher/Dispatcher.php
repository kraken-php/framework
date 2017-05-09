<?php

namespace Kraken\Redis\Dispatcher;

use Kraken\Event\EventEmitter;
use Kraken\Event\EventEmitterInterface;
use Kraken\Redis\ClientStubInterface;
use Kraken\Redis\Command\Traits\Foundation;

class Dispatcher extends EventEmitter implements DispatcherInterface,EventEmitterInterface
{
    use Foundation;

    public function __construct()
    {
        $stub = new ClientStubInterface;
    }
}