<?php

namespace Kraken\Ipc\Socket;

use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\LoopResourceInterface;
use Kraken\Stream\StreamBaseInterface;

/**
 * @event connect(SocketInterface)
 */
interface SocketServerInterface extends EventEmitterInterface, LoopResourceInterface, StreamBaseInterface
{
    /**
     * Get server endpoint.
     *
     * This method returns server endpoint with this pattern [$protocol://$address:$port].
     *
     * @return string
     */
    public function getLocalEndpoint();
}
