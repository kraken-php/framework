<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Throwable\Runtime\RejectionException;

class ProjectStartCommand extends Command implements CommandInterface
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
            ->startProcess(
                $this->config->get('main.alias')
            )
            ->then(
                function() {
                    return 'Project has been started.';
                }
            )
        ;
    }
}
