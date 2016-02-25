<?php

namespace Kraken\Error\Handler\Runtime;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Runtime\RuntimeCommand;
use Error;
use Exception;

class RuntimeContinue extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'origin'
    ];

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->core()->make('Kraken\Runtime\RuntimeChannelInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return $this->channel->send(
            $params['origin'],
            new RuntimeCommand('container:continue')
        );
    }
}
