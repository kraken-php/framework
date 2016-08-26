<?php

namespace Kraken\Runtime\Command\Cmd;

use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;

class CmdPingCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return 'ping';
    }
}
