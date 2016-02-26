<?php

namespace Kraken\Runtime\Command\Process;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ProcessCreateCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']) || !isset($params['name']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->createProcess($params['alias'], $params['name'], (int)$params['flags']);
    }
}
