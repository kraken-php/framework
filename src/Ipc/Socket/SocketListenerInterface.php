<?php

namespace Kraken\Ipc\Socket;

use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\LoopResourceInterface;
use Kraken\Stream\StreamBaseInterface;

/**
 * @event connect : callable(object, SocketInterface)
 */
interface SocketListenerInterface extends EventEmitterInterface, LoopResourceInterface, StreamBaseInterface
{
    /**
     * Stop listener and underlying resource object. It is an alias for close() method.
     *
     * @see StreamBaseInterface::close
     */
    public function stop();

    /**
     * Get listener endpoint.
     *
     * This method returns server endpoint with this pattern [$protocol://$address:$port].
     *
     * @return string
     */
    public function getLocalEndpoint();
}
