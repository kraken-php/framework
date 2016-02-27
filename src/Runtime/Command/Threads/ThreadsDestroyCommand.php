<?php

namespace Kraken\Runtime\Command\Threads;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ThreadsDestroyCommand extends Command implements CommandInterface
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

        return $this->runtime->manager()->destroyThreads($params['aliases'], (int)$params['flags']);
    }
}
