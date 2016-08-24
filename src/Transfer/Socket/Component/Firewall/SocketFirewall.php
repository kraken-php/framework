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
    public function blockAddress($address)
    {
        $this->blacklist[$address] = true;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unblockAddress($address)
    {
        if (isset($this->blacklist[$address]))
        {
            unset($this->blacklist[$address]);
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isAddressBlocked($address)
    {
        return isset($this->blacklist[$address]);
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
    public function handleConnect(TransferConnectionInterface $conn)
    {
        if ($this->isAddressBlocked($conn->getHost()))
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
        if (!$this->isAddressBlocked($conn->getHost()))
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
        if (!$this->isAddressBlocked($conn->getHost()))
        {
            $this->component->handleError($conn, $ex);
        }
    }
}
