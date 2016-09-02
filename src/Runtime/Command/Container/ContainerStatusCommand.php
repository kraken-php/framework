<?php

namespace Kraken\Runtime\Command\Container;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;

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
            'parent' => $runtime->getParent(),
            'alias'  => $runtime->getAlias(),
            'name'   => $runtime->getName(),
            'state'  => $runtime->getState()
        ];
    }
}
