<?php

namespace Kraken\Io\Websocket\Driver\Version;

use Kraken\Io\Http\HttpRequestInterface;

interface VersionInterface
{
    /**
     * Given an HTTP header, determine if this version should handle the protocol.
     *
     * @param HttpRequestInterface $request
     * @return bool
     */
    public function isRequestSupported(HttpRequestInterface $request);

    /**
     * Return numberic identificator of the protocol.
     *
     * @return int
     */
    public function getVersionNumber();

//    /**
//     * Perform the handshake and return the response headers
//     * @param \Guzzle\Http\Message\RequestInterface $request
//     * @return \Guzzle\Http\Message\Response
//     * @throws \UnderflowException If the message hasn't finished buffering (not yet implemented, theoretically will only happen with Hixie version)
//     */
//    function handshake(RequestInterface $request);
//
//    /**
//     * @param  \Ratchet\ConnectionInterface $conn
//     * @param  \Ratchet\MessageInterface    $coalescedCallback
//     * @return \Ratchet\ConnectionInterface
//     */
//    function upgradeConnection(ConnectionInterface $conn, MessageInterface $coalescedCallback);
}
