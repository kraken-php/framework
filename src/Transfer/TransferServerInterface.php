<?php

namespace Kraken\Transfer;

use Kraken\Loop\LoopResourceInterface;

interface TransferServerInterface extends LoopResourceInterface
{
    /**
     * Close the underlying SocketListner.
     */
    public function stop();

    /**
     * Close the underlying SocketListner.
     */
    public function close();

    /**
     * Add an endpoint/application to the server.
     *
     * @param string $path
     * @param ServerComponentInterface $component
     * @return TransferServerInterface
     */
    public function addRoute($path, ServerComponentInterface $component);

    /**
     * Remote endpoint/application from the server.
     *
     * @param string $path
     * @return TransferServerInterface
     */
    public function removeRoute($path);

    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     *
     * @param string $address
     * @return TransferServerInterface
     */
    public function blockAddress($address);

    /**
     * Unblock an address so they can access your application again.
     *
     * @param string $address
     * @return TransferServerInterface
     */
    public function unblockAddress($address);

    /**
     * Check if given $address is blocked or not.
     *
     * @param string $address
     * @return bool
     */
    public function isAddressBlocked($address);

    /**
     * Get an array of all the addresses blocked.
     *
     * @return string[]
     */
    public function getBlockedAddresses();
}
