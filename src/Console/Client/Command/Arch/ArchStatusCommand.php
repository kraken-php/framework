<?php

namespace Kraken\Console\Client\Command\Arch;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\ConsoleCommand;

class ArchStatusCommand extends ConsoleCommand
{
    /**
     * @param mixed $value
     * @return mixed
     */
    protected function onMessage($value)
    {
        return $this->buildTable($value);
    }

    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('arch:status')
            ->setDescription('Checks status of part of architecture.')
        ;

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of root container to be checked.'
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

        $cmd  = 'arch:status';
        $opts = [];

        return [ $alias, $cmd, $opts ];
    }

    /**
     * @param mixed $current
     * @param int $cnt
     * @return mixed
     */
    private function buildTable($current, $cnt = 0)
    {
        $data = [];

        $data[] = [
            'alias' => str_repeat('_', $cnt) . $current['alias'],
            'name'  => str_repeat('_', $cnt) . $current['name'],
            'state' => $this->parseState($current['state'])
        ];

        foreach ($current['children'] as $child)
        {
            $data = array_merge($data, $this->buildTable($child, $cnt+2));
        }

        return $data;
    }

    /**
     * @param int $state
     * @return string
     */
    private function parseState($state)
    {
        switch ($state)
        {
            case Runtime::STATE_CREATED:    return 'CREATED';
            case Runtime::STATE_DESTROYED:  return 'DESTROYED';
            case Runtime::STATE_STARTED:    return 'STARTED';
            case Runtime::STATE_STOPPED:    return 'STOPPED';
            default:                        return 'UNKNOWN';
        }
    }
}
