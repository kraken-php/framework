<?php

namespace Kraken\Runtime\Command\Process;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Runtime\RejectionException;

class ProcessDestroyCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->destroyProcess($params['alias'], (int)$params['flags']);
    }
}
