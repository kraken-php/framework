<?php

namespace Kraken\Console\Client\Command\Project;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProjectStopCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('project:stop')
            ->setDescription('Stops project using project.config configuration.')
        ;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $cmd  = 'project:stop';
        $opts = [];

        return $this->informServer(null, $cmd, $opts);
    }
}
