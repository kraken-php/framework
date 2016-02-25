<?php

namespace Kraken\Io\Socket\Component\Firewall;

use Kraken\Io\IoConnectionInterface;
use Kraken\Io\IoMessageInterface;
use Kraken\Io\IoServerComponentInterface;
use Exception;

class SocketFirewall implements SocketFirewallInterface
{
    /**
     * @var IoServerComponentInterface
     */
    protected $component;

    /**
     * @var string[]
     */
    protected $blacklist;

    /**
     * @param IoServerComponentInterface $component
     */
    public function __construct(IoServerComponentInterface $component)
    {
        $this->component = $component;
        $this->blacklist = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->component);
        unset($this->blacklist);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function blockAddress($ip)
    {
        $this->blacklist[$ip] = true;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unblockAddress($ip)
    {
        if (isset($this->blacklist[$ip]))
        {
            unset($this->blacklist[$ip]);
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isBlocked($ip)
    {
        return isset($this->blacklist[$ip]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBlockedAddresses()
    {
        return array_keys($this->blacklist);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function filterConnect(IoConnectionInterface $conn)
    {
        return !$this->isBlocked($conn->getHost());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(IoConnectionInterface $conn)
    {
        if ($this->isBlocked($conn->getHost()))
        {
            return $conn->close();
        }

        return $this->component->handleConnect($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(IoConnectionInterface $conn)
    {
        if (!$this->isBlocked($conn->getHost()))
        {
            $this->component->handleDisconnect($conn);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(IoConnectionInterface $conn, IoMessageInterface $message)
    {
        return $this->component->handleMessage($conn, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(IoConnectionInterface $conn, $ex)
    {
        if (!$this->isBlocked($conn->getHost()))
        {
            $this->component->handleError($conn, $ex);
        }
    }
}
