<?php

namespace Kraken\Console\Client\Command\Thread;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\ConsoleCommand;

class ThreadExistsCommand extends ConsoleCommand
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('thread:exists')
            ->setDescription('Checks if thread with given alias exists in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of thread to be checked.'
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

        $cmd  = 'process:exists';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }

    /**
     * @param mixed $value
     */
    protected function onSuccess($value)
    {
        $value = (bool) $value;

        if ($value)
        {
            echo $this->successMessage("Thread exists.");
        }
        else
        {
            echo $this->failureMessage("Thread does not exist.");
        }
    }
}
