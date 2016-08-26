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
     * @override
     * @inheritDoc
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
            Runtime::DESTROY_FORCE_SOFT
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $flags  = $input->getOption('flags');

        $flags  = $this->validateDestroyFlags($flags);

        $cmd  = 'project:destroy';
        $opts = [
            'flags' => $flags
        ];

        return [ null, $cmd, $opts ];
    }
}
