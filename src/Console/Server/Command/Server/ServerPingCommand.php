<?php

namespace Kraken\Console\Server\Command\Server;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;

class ServerPingCommand extends Command implements CommandInterface
{
    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return gethostbyname(gethostname());
    }
}
