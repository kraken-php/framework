<?php

namespace Kraken\Console\Client\Command\Project;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProjectStartCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('project:start')
            ->setDescription('Starts project using project.config configuration.')
        ;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $cmd  = 'project:start';
        $opts = [];

        return $this->informServer(null, $cmd, $opts);
    }
}
