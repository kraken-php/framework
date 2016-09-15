<?php

namespace Kraken\Runtime\Supervision\Cmd;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Runtime\RuntimeCommand;
use Error;
use Exception;

class CmdEscalateManager extends Solver implements SolverInterface
{
    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $parent;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->getCore()->make('Kraken\Runtime\Service\ChannelInternal');
        $this->parent  = $this->runtime->getParent();
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
        unset($this->parent);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->parent,
            new RuntimeCommand('cmd:error', [ 'exception' => get_class($ex), 'message' => $ex->getMessage() ])
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
}
