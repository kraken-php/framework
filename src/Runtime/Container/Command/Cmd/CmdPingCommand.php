<?php

namespace Kraken\Runtime\Container\Command\Cmd;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;

class CmdPingCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        return 'ping';
    }
}
