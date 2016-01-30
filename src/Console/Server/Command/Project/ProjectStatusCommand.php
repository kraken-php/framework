<?php

namespace Kraken\Console\Server\Command\Project;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Command\Command;
use Kraken\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Runtime\RuntimeCommand;

class ProjectStatusCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $config = $this->runtime->core()->make('Kraken\Config\ConfigInterface');

        $this->channel = $this->runtime->core()->make('Kraken\Runtime\RuntimeChannelInterface');
        $this->config = new Config($config->get('core.project'));
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
        unset($this->config);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        $req = new Request(
            $this->channel,
            $this->config->get('main.alias'),
            new RuntimeCommand('arch:status')
        );

        return $req->call();
    }
}
