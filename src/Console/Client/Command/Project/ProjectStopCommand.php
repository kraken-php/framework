<?php

namespace Kraken\Console\Client\Command\Project;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProjectStopCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('project:stop')
            ->setDescription('Stops project using core.project configuration.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $cmd  = 'project:stop';
        $opts = [];

        return [ null, $cmd, $opts ];
    }
}
