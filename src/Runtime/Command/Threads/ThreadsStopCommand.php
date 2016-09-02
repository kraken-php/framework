<?php

namespace Kraken\Runtime\Command\Threads;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ThreadsStopCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->getManager()->stopThreads($params['aliases']);
    }
}
