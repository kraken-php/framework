<?php

namespace Kraken\Console\Client\Command\Container;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ContainerStatusCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('container:status')
            ->setDescription('Checks status of container.')
        ;

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of container to be checked.'
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

        $cmd  = 'container:status';
        $opts = [];

        return [ $alias, $cmd, $opts ];
    }
}
