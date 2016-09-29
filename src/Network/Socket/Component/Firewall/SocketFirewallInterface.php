<?php

namespace Kraken\Network\Socket\Component\Firewall;

use Kraken\Network\NetworkComponentInterface;

interface SocketFirewallInterface extends NetworkComponentInterface
{
    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     *
     * @param string $address
     * @return SocketFirewallInterface
     */
    public function blockAddress($address);

    /**
     * Unblock an address so they can access your application again.
     *
     * @param string $address
     * @return SocketFirewallInterface
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
