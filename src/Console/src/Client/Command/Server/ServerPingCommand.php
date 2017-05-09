<?php

namespace Kraken\Console\Client\Command\Server;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ServerPingCommand extends Command
{
    /**
     * @param mixed $value
     * @return mixed
     */
    protected function onMessage($value)
    {
        return "ip=$value";
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('server:ping')
            ->setDescription('Pings client-server connection.')
        ;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $cmd  = 'server:ping';
        $opts = [];

        return $this->informServer(null, $cmd, $opts);
    }
}
