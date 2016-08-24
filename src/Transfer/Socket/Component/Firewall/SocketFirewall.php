<?php

namespace Kraken\Transfer\Socket\Component\Firewall;

use Kraken\Transfer\Null\NullServer;
use Kraken\Transfer\ServerComponentAwareInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\ServerComponentInterface;

class SocketFirewall implements SocketFirewallInterface, ServerComponentAwareInterface
{
    /**
     * @var ServerComponentInterface
     */
    protected $component;

    /**
     * @var string[]
     */
    protected $blacklist;

    /**
     * @param ServerComponentAwareInterface|null $aware
     * @param ServerComponentInterface|null $component
     */
    public function __construct(ServerComponentAwareInterface $aware = null, ServerComponentInterface $component = null)
    {
        $this->aware = $aware;
        $this->component = $component;
        $this->blacklist = [];

        if ($aware !== null)
        {
            $aware->setComponent($this);
        }
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
    public function setComponent(ServerComponentInterface $component = null)
    {
        $this->component = $component === null ? new NullServer() : $component;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getComponent()
    {
        return $this->component;
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
    public function filterConnect(TransferConnectionInterface $conn)
    {
        return !$this->isBlocked($conn->getHost());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(TransferConnectionInterface $conn)
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
    public function handleDisconnect(TransferConnectionInterface $conn)
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
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
    {
        return $this->component->handleMessage($conn, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(TransferConnectionInterface $conn, $ex)
    {
        if (!$this->isBlocked($conn->getHost()))
        {
            $this->component->handleError($conn, $ex);
        }
    }
}
