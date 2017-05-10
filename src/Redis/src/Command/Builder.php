<?php

namespace Kraken\Redis\Command;

use Kraken\Redis\Protocol\Data\Request;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;
use Kraken\Redis\Protocol\Resp;
use Kraken\Promise\Deferred;
use Kraken\Redis\Command\Traits\Foundation;
use Kraken\Redis\Protocol\Model\ModelInterface;


class Builder
{
    private $command;
    private $args;

    use Foundation;

    public function build()
    {
        return new Request($this->command, $this->args);
    }
}