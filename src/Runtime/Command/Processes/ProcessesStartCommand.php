<?php

namespace Kraken\Runtime\Command\Processes;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Exception\Runtime\RejectionException;

class ProcessesStartCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->startProcesses($params['aliases']);
    }
}
