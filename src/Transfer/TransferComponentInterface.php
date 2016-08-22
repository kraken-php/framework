<?php

namespace Kraken\Transfer;

use Kraken\Transfer\Http\HttpRequestInterface;
use Error;
use Exception;

interface TransferComponentInterface
{
    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param TransferConnectionInterface $conn
     * @throws Exception
     */
    public function handleConnect(TransferConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed
     *
     * @param TransferConnectionInterface $conn The socket/connection that is closing/closed
     * @throws Exception
     */
    public function handleDisconnect(TransferConnectionInterface $conn);

    /**
     * Triggered when a client sends data through the socket.
     *
     * @param TransferConnectionInterface $conn
     * @param TransferMessageInterface|HttpRequestInterface $message
     * @throws Exception
     */
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown, the
     * Exception is sent back down the stack, handled by the Server and bubbled back up the application through this
     * method.
     *
     * @param TransferConnectionInterface $conn
     * @param Error|Exception $ex
     * @throws Exception
     */
    public function handleError(TransferConnectionInterface $conn, $ex);
}
