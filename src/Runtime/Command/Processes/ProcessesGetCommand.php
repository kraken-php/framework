<?php

namespace Kraken\Runtime\Command\Processes;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProcessesGetCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return $this->runtime->getManager()->getProcesses();
    }
}
