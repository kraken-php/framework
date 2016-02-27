<?php

namespace Kraken\Runtime\Command\Processes;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ProcessesGetCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        return $this->runtime->manager()->getProcesses();
    }
}
