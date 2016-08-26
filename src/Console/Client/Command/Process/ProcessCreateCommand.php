<?php

namespace Kraken\Console\Client\Command\Process;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProcessCreateCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('process:create')
            ->setDescription('Creates process name with given alias in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of new process.'
        );

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Name of new process.'
        );

        $this->addOption(
            'flags',
            null,
            InputOption::VALUE_OPTIONAL,
            'Force level.',
            Runtime::CREATE_DEFAULT
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
        $name   = $input->getArgument('name');
        $flags  = $input->getOption('flags');

        $flags  = $this->validateCreateFlags($flags);

        $cmd  = 'process:create';
        $opts = [
            'alias' => $alias,
            'name'  => $name,
            'flags' => $flags
        ];

        return [ $parent, $cmd, $opts ];
    }
}
