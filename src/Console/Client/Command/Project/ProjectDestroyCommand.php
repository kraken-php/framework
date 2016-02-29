<?php

namespace Kraken\Console\Client\Command\Project;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProjectDestroyCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('project:destroy')
            ->setDescription('Destroys project using core.project configuration.')
        ;

        $this->addOption(
            'flags',
            null,
            InputOption::VALUE_OPTIONAL,
            'Force level.',
            Runtime::DESTROY_FORCE
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $flags  = $input->getOption('flags');

        $cmd  = 'project:destroy';
        $opts = [
            'flags' => $flags
        ];

        return [ null, $cmd, $opts ];
    }
}
