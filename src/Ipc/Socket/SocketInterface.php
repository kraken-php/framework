<?php

namespace Kraken\Ipc\Socket;

use Kraken\Stream\AsyncStreamInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

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

    /**
     * Get socket local host.
     *
     * @return string
     */
    public function getLocalHost();

    /**
     * Get socket remote host.
     *
     * @return string
     */
    public function getRemoteHost();

    /**
     * Get socket local port.
     *
     * @return string
     */
    public function getLocalPort();

    /**
     * Get socket remote port.
     *
     * @return string
     */
    public function getRemotePort();

    /**
     * Get socket local protocol.
     *
     * @return string
     */
    public function getLocalProtocol();

    /**
     * Get socket remote protocol.
     *
     * @return string
     */
    public function getRemoteProtocol();

    /**
     * Return bool indicating whether the listener is encrypted.
     *
     * @return bool
     */
    public function isEncrypted();
}
