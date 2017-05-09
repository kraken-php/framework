<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Runtime\RuntimeCommand;

class ProjectStatusCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $core = $this->runtime->getCore();

        $config  = $core->make('Kraken\Config\ConfigInterface');
        $channel = $core->make('Kraken\Runtime\Service\ChannelInternal');

        $this->config  = $this->createConfig($config);
        $this->channel = $channel;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->channel);
        unset($this->config);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->config->get('main.alias'),
            new RuntimeCommand('arch:status')
        );

        return $req->call();
    }

    /**
     * Create Request.
     *
     * @param ChannelInterface $channel
     * @param string $receiver
     * @param string $command
     * @return Request
     */
    protected function createRequest(ChannelInterface $channel, $receiver, $command)
    {
        return new Request($channel, $receiver, $command);
    }

    /**
     * Create Config.
     *
     * @param ConfigInterface|null $config
     * @return Config
     */
    protected function createConfig(ConfigInterface $config = null)
    {
        return new Config($config === null ? [] : $config->get('project.config'));
    }
}
