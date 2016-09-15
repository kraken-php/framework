<?php

namespace Kraken\Runtime\Supervision\Runtime;

use Kraken\Channel\ChannelInterface;
use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Runtime\RuntimeCommand;
use Error;
use Exception;

class RuntimeContinue extends Solver implements SolverInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'origin'
    ];

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->getCore()->make('Kraken\Runtime\Service\ChannelInternal');
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
    protected function solver($ex, $params = [])
    {
        return $this->channel->send(
            $params['origin'],
            new RuntimeCommand('container:continue')
        );
    }
}
