<?php

namespace Kraken\Runtime\Command\Process;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProcessExistsCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->getManager()->existsProcess($params['alias']);
    }
}
