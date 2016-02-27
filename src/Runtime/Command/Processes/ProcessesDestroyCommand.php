<?php

namespace Kraken\Runtime\Command\Processes;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ProcessesDestroyCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->destroyProcesses($params['aliases'], (int)$params['flags']);
    }
}
