<?php

namespace Kraken\Runtime\Command\Threads;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Exception\Runtime\RejectionException;

class ThreadsGetCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        return $this->runtime->manager()->getThreads();
    }
}
