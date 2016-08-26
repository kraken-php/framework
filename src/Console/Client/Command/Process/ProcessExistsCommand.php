<?php

namespace Kraken\Console\Client\Command\Process;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProcessExistsCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('process:exists')
            ->setDescription('Checks if process with given alias exists in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of process to check.'
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

        $cmd  = 'process:exists';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function onSuccess($value)
    {
        $value = (bool) $value;

        if ($value)
        {
            echo $this->successMessage("Process exists.");
        }
        else
        {
            echo $this->failureMessage(new Exception, "Process does not exist.");
        }
    }
}
