<?php

namespace Kraken\Runtime\Command\Processes;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProcessesDestroyCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->getManager()->destroyProcesses($params['aliases'], (int)$params['flags']);
    }
}
