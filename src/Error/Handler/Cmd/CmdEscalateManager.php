<?php

namespace Kraken\Error\Handler\Cmd;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Runtime\RuntimeCommand;
use Error;
use Exception;

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
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $req = new Request(
            $this->channel,
            $this->parent,
            new RuntimeCommand('cmd:error', [ 'exception' => get_class($ex), 'message' => $ex->getMessage() ])
        );

        return $req->call();
    }
}
