<?php

namespace Kraken\Runtime\Command\Runtime;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Throwable\Runtime\RejectionException;

class RuntimeExistsCommand extends Command implements CommandInterface
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

        return $this->runtime->manager()->existsRuntime($params['alias']);
    }
}
