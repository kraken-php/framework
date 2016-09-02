<?php

namespace Kraken\Runtime\Command\Container;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;

class ContainerStopCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return $this->runtime->stop();
    }
}
