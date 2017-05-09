<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Console\Server\Manager\ProjectManagerInterface;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProjectDestroyCommand extends Command implements CommandInterface
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
        if (!isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->manager
            ->destroyProject($params['flags'])
            ->then(
                function() {
                    return 'Project has been destroyed.';
                }
            )
        ;
    }
}
