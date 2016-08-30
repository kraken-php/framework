<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Runtime\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProjectStopCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $config = $this->runtime->getCore()->make('Kraken\Config\ConfigInterface');

        $this->config = $this->createConfig($config);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->config);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        return $this->runtime
            ->manager()
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

    /**
     * Create Config.
     *
     * @param ConfigInterface|null $config
     * @return Config
     */
    protected function createConfig(ConfigInterface $config = null)
    {
        return new Config($config === null ? [] : $config->get('core.project'));
    }
}
