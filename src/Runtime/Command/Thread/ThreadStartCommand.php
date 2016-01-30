<?php

namespace Kraken\Runtime\Command\Thread;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Exception\Runtime\RejectionException;

class ThreadStartCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->startThread($params['alias']);
    }
}
