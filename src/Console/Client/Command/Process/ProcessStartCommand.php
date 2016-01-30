<?php

namespace Kraken\Console\Client\Command\Process;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\ConsoleCommand;

class ProcessStartCommand extends ConsoleCommand
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('process:start')
            ->setDescription('Sends start signal to process with given alias from parent.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of process to be started.'
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

        $cmd  = 'process:start';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }
}
