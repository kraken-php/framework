<?php

namespace Kraken\Runtime\Supervisor\Runtime;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Supervisor\SolverBase;
use Kraken\Supervisor\SolverInterface;
use Kraken\Runtime\RuntimeCommand;
use Error;
use Exception;

class RuntimeContinue extends SolverBase implements SolverInterface
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
        $this->channel = $this->runtime->core()->make('Kraken\Runtime\Channel\ChannelInterface');
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
