<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Console\Server\Manager\ProjectManagerInterface;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;

class ProjectStopCommand extends Command implements CommandInterface
{
    /**
     * @var ProjectManagerInterface
     */
    protected $manager;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $this->manager = $this->runtime->getCore()->make('Project.Manager');
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->manager);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return $this->manager
            ->stopProject()
            ->then(
                function() {
                    return 'Project has been stopped.';
                }
            )
        ;
    }
}
