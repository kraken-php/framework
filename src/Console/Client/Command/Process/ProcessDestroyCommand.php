<?php

namespace Kraken\Console\Client\Command\Process;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProcessDestroyCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('process:destroy')
            ->setDescription('Destroys process with given alias in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of process to be destroyed.'
        );

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
        $parent = $input->getArgument('parent');
        $alias  = $input->getArgument('alias');
        $flags  = $input->getOption('flags');

        $cmd  = 'process:destroy';
        $opts = [
            'alias' => $alias,
            'flags' => $flags
        ];

        return [ $parent, $cmd, $opts ];
    }
}
