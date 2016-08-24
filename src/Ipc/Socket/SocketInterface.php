<?php

namespace Kraken\Ipc\Socket;

use Kraken\Stream\AsyncStreamInterface;

interface SocketInterface extends AsyncStreamInterface
{
    /**
     * Stop listener and underlying resource object. It is an alias for close() method.
     *
     * @see StreamBaseInterface::close
     */
    public function stop();

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

    /**
     * Get socket local address.
     *
     * @return string
     */
    public function getLocalAddress();

    /**
     * Get socket remote address.
     *
     * @return string
     */
    public function getRemoteAddress();
}
