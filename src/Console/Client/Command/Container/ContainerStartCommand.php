<?php

namespace Kraken\Console\Client\Command\Container;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\ConsoleCommand;

class ContainerStartCommand extends ConsoleCommand
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('container:start')
            ->setDescription('Starts container with given alias.')
        ;

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of container to be started.'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $alias  = $input->getArgument('alias');

        $cmd  = 'container:start';
        $opts = [];

        return [ $alias, $cmd, $opts ];
    }
}
