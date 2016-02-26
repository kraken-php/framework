<?php

namespace Kraken\Runtime\Container\Command\Container;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;

class ContainerStatusCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;

        return [
            'parent' => $runtime->parent(),
            'alias'  => $runtime->alias(),
            'name'   => $runtime->name(),
            'state'  => $runtime->state()
        ];
    }
}
