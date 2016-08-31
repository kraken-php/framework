<?php

namespace Kraken\Runtime\Command\Threads;

use Kraken\Command\CommandInterface;
use Kraken\Runtime\Command\Command;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ThreadsCreateCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        if (!isset($params['definitions']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->getManager()->createThreads($params['definitions'], (int)$params['flags']);
    }
}
