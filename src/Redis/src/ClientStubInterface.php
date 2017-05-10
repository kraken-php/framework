<?php

namespace Kraken\Redis;


use Kraken\Redis\Command\FoundationInterface;
use Kraken\Redis\Dispatcher\DispatcherInterface;
use Kraken\Redis\Protocol\Model\ModelInterface;

interface ClientStubInterface extends DispatcherInterface,FoundationInterface
{
    public function handleMessage(ModelInterface $message);

    public function close();
}