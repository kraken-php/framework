<?php

namespace Kraken\Console\Client\Command\Thread;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\ConsoleCommand;

class ThreadStopCommand extends ConsoleCommand
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('thread:stop')
            ->setDescription('Sends stop signal to thread with given alias from parent.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of thread to be stopped.'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $parent = $input->getArgument('parent');
        $alias  = $input->getArgument('alias');

        $cmd  = 'thread:stop';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }
}
