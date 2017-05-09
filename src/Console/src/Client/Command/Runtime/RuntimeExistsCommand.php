<?php

namespace Kraken\Console\Client\Command\Runtime;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class RuntimeExistsCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('runtime:exists')
            ->setDescription('Checks if runtime with given alias exists in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of runtime to check.'
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

        $cmd  = 'runtime:exists';
        $opts = [
            'alias' => $alias
        ];

        return $this->informServer($parent, $cmd, $opts);
    }

    /**
     * @param mixed $value
     */
    protected function onSuccess($value)
    {
        $value = (bool) $value;

        if ($value)
        {
            echo $this->successMessage("Runtime exists.");
        }
        else
        {
            echo $this->failureMessage(new Exception(), "Runtime does not exist.");
        }
    }
}
