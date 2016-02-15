<?php

namespace Kraken\Ipc\Socket;

use Kraken\Stream\StreamAsyncInterface;

interface SocketInterface extends StreamAsyncInterface
{
    /**
     * Get socket local endpoint.
     *
     * This method returns socket local endpoint with this pattern [$protocol://$address:$port].
     *
     * @return string
     */
    public function getLocalEndpoint();

    /**
     * Get socket remote endpoint.
     *
     * This method returns socket remote endpoint with this pattern [$protocol://$address:$port].
     *
     * @return string
     */
    public function getRemoteEndpoint();
}
