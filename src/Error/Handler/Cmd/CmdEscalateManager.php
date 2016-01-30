<?php

namespace Kraken\Error\Handler\Cmd;

use Exception;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Runtime\RuntimeCommand;

class CmdEscalateManager extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @var ChannelBaseInterface
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
        $this->channel = $this->runtime->core()->make('Kraken\Runtime\RuntimeChannelInterface');
        $this->parent  = $this->runtime->parent();
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
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler(Exception $ex, $params = [])
    {
        $req = new Request(
            $this->channel,
            $this->parent,
            new RuntimeCommand('cmd:error', [ 'exception' => get_class($ex), 'message' => $ex->getMessage() ])
        );

        return $req->call();
    }
}
