<?php

namespace Kraken\Network;

use Kraken\Network\Http\HttpRequestInterface;
use Error;
use Exception;

interface ServerComponentInterface
{
    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param NetworkConnectionInterface $conn
     * @throws Exception
     */
    public function handleConnect(NetworkConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed
     *
     * @param NetworkConnectionInterface $conn The socket/connection that is closing/closed
     * @throws Exception
     */
    public function handleDisconnect(NetworkConnectionInterface $conn);

    /**
     * Triggered when a client sends data through the socket.
     *
     * @param NetworkConnectionInterface $conn
     * @param NetworkMessageInterface|HttpRequestInterface $message
     * @throws Exception
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown, the
     * Exception is sent back down the stack, handled by the Server and bubbled back up the application through this
     * method.
     *
     * @param NetworkConnectionInterface $conn
     * @param Error|Exception $ex
     * @throws Exception
     */
    public function handleError(NetworkConnectionInterface $conn, $ex);
}
