<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ProjectStopCommand extends Command implements CommandInterface
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
        return $this->runtime->manager()
            ->stopProcess(
                $this->config->get('main.alias')
            )
            ->then(
                function() {
                    return 'Project has been stopped.';
                }
            )
        ;
    }
}
