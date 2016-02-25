<?php

namespace Kraken\Io;

use Kraken\Io\Http\HttpRequestInterface;
use Exception;

interface IoServerComponentInterface
{
    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param IoConnectionInterface $conn
     * @throws Exception
     */
    public function handleConnect(IoConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed
     *
     * @param IoConnectionInterface $conn The socket/connection that is closing/closed
     * @throws Exception
     */
    public function handleDisconnect(IoConnectionInterface $conn);

    /**
     * Triggered when a client sends data through the socket.
     *
     * @param IoConnectionInterface $conn
     * @param IoMessageInterface|HttpRequestInterface $message
     * @throws Exception
     */
    public function handleMessage(IoConnectionInterface $conn, IoMessageInterface $message);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param IoConnectionInterface $conn
     * @param Exception $ex
     * @throws Exception
     */
    public function handleError(IoConnectionInterface $conn, Exception $ex);
}
