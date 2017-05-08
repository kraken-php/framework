<?php

namespace Kraken\Network;

use Kraken\Loop\LoopResourceInterface;

interface NetworkServerInterface extends LoopResourceInterface
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
     * Check if there exists any endpoint/application to the server on given route.
     *
     * @param string $path
     * @return bool
     */
    public function existsRoute($path);

    /**
     * Add an endpoint/application to the server.
     *
     * @param string $path
     * @param NetworkComponentInterface $component
     * @return NetworkServerInterface
     */
    public function addRoute($path, NetworkComponentInterface $component);

    /**
     * Remote endpoint/application from the server.
     *
     * @param string $path
     * @return NetworkServerInterface
     */
    public function removeRoute($path);

    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     *
     * @param string $address
     * @return NetworkServerInterface
     */
    public function blockAddress($address);

    /**
     * Unblock an address so they can access your application again.
     *
     * @param string $address
     * @return NetworkServerInterface
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
