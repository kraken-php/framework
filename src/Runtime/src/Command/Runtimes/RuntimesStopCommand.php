<?php

namespace Kraken\Runtime\Command\Runtimes;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class RuntimesStopCommand extends Command implements CommandInterface
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

        return $this->runtime->getManager()->stopRuntimes($params['aliases']);
    }
}
