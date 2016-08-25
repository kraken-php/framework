<?php

namespace Kraken\Network\Websocket\Driver\Version\RFC6455;

use Kraken\Network\NetworkMessage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;

class OnMessageProxy implements MessageInterface
{
    protected $target;

    /**
     * @param callable $target
     */
    public function __construct(callable $target)
    {
        $this->target = $target;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->target);
    }

    /**
     * @override
     * @inheritDoc
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        $callable = $this->target;
        $callable($from, new NetworkMessage($msg));
    }
}
