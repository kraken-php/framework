<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Console\Server\Manager\ProjectManagerInterface;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

class ProjectCreateCommand extends Command implements CommandInterface
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
            ->createProject($params['flags'])
            ->then(
                function() {
                    return 'Project has been created.';
                }
            )
        ;
    }
}
