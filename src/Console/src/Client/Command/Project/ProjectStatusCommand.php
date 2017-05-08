<?php

namespace Kraken\Console\Client\Command\Project;

use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kraken\Console\Client\Command\Command;

class ProjectStatusCommand extends Command
{
    /**
     * @override
     * @inheritDoc
     */
    protected function onMessage($value)
    {
        return $this->buildTable($value);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function config()
    {
        $this
            ->setName('project:status')
            ->setDescription('Checks status of project using project.config configuration.')
        ;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $cmd  = 'project:status';
        $opts = [];

        return $this->informServer(null, $cmd, $opts);
    }

    /**
     * @param mixed $current
     * @param string $prefix
     * @param bool $isLast
     * @return mixed
     */
    private function buildTable($current, $prefix = '', $isLast = false)
    {
        $data = [];
        $currentPrefix = ($prefix === '') ? '' : substr($prefix, 0, -2) . ($isLast ? '└-' : '├-');

        $data[] = [
            'alias' => $currentPrefix . $current['alias'],
            'name'  => $current['name'],
            'state' => $this->parseState($current['state'])
        ];

        $count = count($current['children']);

        for ($i=0; $i<$count; ++$i)
        {
            $child = $current['children'][$i];

            if ($i === $count-1)
            {
                $data = array_merge($data, $this->buildTable($child, $prefix . '  ', true));
            }
            else
            {
                $data = array_merge($data, $this->buildTable($child, $prefix . '| '));
            }
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
