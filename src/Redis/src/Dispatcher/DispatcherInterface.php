<?php

namespace Kraken\Redis\Dispatcher;

use Kraken\Event\EventEmitterInterface;
use Kraken\Redis\Protocol\Data\Request;

interface DispatcherInterface extends EventEmitterInterface
{
    public function dispatch(Request $command);
}