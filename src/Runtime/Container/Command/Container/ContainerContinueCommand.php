<?php

namespace Kraken\Runtime\Container\Command\Container;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;

class ContainerContinueCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        $this->runtime->succeed();
    }
}
