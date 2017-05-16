<?php

namespace Kraken\Redis;

use Kraken\Redis\Protocol\Data\Request;
use Kraken\Event\EventEmitterInterface;
use Kraken\Redis\Command\CommandInterface;
use Kraken\Redis\Protocol\Model\ModelInterface;

interface ClientStubInterface extends EventEmitterInterface,CommandInterface
{
    public function dispatch(Request $command);
    public function handleMessage(ModelInterface $message);
}