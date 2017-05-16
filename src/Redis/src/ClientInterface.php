<?php

namespace Kraken\Redis;

use Kraken\Event\EventEmitterInterface;
use Kraken\Promise\PromiseInterface;

interface ClientInterface extends EventEmitterInterface
{
    public function close();
}
