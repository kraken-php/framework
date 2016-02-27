<?php

namespace Kraken\Runtime\Command\Container;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;

class ContainerStartCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        return $this->runtime->start();
    }
}
