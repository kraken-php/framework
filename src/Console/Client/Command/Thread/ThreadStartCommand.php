<?php

namespace Kraken\Console\Client\Command\Thread;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ThreadStartCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('thread:start')
            ->setDescription('Sends start signal to thread with given alias from parent.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of runtime to be started.'
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

        $cmd  = 'thread:start';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }
}
