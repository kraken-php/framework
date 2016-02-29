<?php

namespace Kraken\Console\Client\Command\Runtime;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class RuntimeStopCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('runtime:stop')
            ->setDescription('Sends stop signal to runtime with given alias from parent.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of runtime to be stopped.'
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

        $cmd  = 'runtime:stop';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }
}
