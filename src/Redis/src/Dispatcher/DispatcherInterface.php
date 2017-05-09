<?php

namespace Kraken\Redis\Dispatcher;

use Kraken\Redis\Command\Command;
use Kraken\Redis\Command\FoundationInterface;

interface DispatcherInterface extends FoundationInterface
{
    public function dispatch(Command $command);
}