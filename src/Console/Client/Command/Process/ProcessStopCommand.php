<?php

namespace Kraken\Console\Client\Command\Process;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProcessStopCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('process:stop')
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
            'Alias of process to be stopped.'
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

        $cmd  = 'process:create';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }
}
