<?php

namespace Kraken\Runtime\Command\Container;

use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;

class ContainerStatusCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
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
