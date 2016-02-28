<?php

namespace Kraken\Transfer;

use Ratchet\ConnectionInterface as RatchetConnectionInterface;

interface IoConnectionInterface extends RatchetConnectionInterface
{
    /**
     * Get the resource id of connection.
     *
     * @return int
     */
    public function getResourceId();

    /**
     * Get the remote endpoint of connection.
     *
     * @return string
     */
    public function getEndpoint();

    /**
     * Get the remote address of connection.
     *
     * @return string
     */
    public function getAddress();

    /**
     * Get the remote host of connection.
     *
     * @return string
     */
    public function getHost();

    /**
     * Get the remote port of connection.
     *
     * @return string
     */
    public function getPort();

    /**
     * Send data to the connection.
     *
     * @param  string $data
     */
    public function send($data);

    /**
     * Close the connection.
     */
    public function close();
}
