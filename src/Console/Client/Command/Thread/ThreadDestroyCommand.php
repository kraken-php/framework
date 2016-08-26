<?php

namespace Kraken\Console\Client\Command\Thread;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ThreadDestroyCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('thread:destroy')
            ->setDescription('Destroys thread with given alias in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of thread to be destroyed.'
        );

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
        $parent = $input->getArgument('parent');
        $alias  = $input->getArgument('alias');
        $flags  = $input->getOption('flags');

        $cmd  = 'thread:destroy';
        $opts = [
            'alias' => $alias,
            'flags' => $flags
        ];

        return [ $parent, $cmd, $opts ];
    }
}
