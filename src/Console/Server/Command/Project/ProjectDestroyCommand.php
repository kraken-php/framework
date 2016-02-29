<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ProjectDestroyCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     *
     */
    protected function construct()
    {
        $config = $this->runtime->core()->make('Kraken\Config\ConfigInterface');
        $this->config = new Config($config->get('core.project'));
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->config);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()
            ->destroyProcess(
                $this->config->get('main.alias'),
                $params['flags']
            )
            ->then(
                function() {
                    return 'Project has been destroyed.';
                }
            )
        ;
    }
}
