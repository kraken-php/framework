<?php

namespace Kraken\Transfer\Http\Component\Router;

use Kraken\Transfer\IoServerComponentInterface;

interface HttpRouterInterface extends IoServerComponentInterface
{
    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     *
     * @param string $address
     * @return HttpRouterInterface
     */
    public function blockAddress($address);

    /**
     * Unblock an address so they can access your application again.
     *
     * @param string $address
     * @return HttpRouterInterface
     */
    public function unblockAddress($address);

    /**
     * Check if given $ip is blocked or not.
     *
     * @param string $address
     * @return bool
     */
    public function isBlocked($address);

    /**
     * Get an array of all the addresses blocked.
     *
     * @return string[]
     */
    public function getBlockedAddresses();

    /**
     * Add an endpoint/application to the server.
     *
     * @param string $path
     * @param IoServerComponentInterface $component
     * @return HttpRouterInterface
     */
    public function addRoute($path, IoServerComponentInterface $component);

    /**
     * Remote endpoint/application from the server.
     *
     * @param string $path
     * @return HttpRouterInterface
     */
    public function removeRoute($path);
}
