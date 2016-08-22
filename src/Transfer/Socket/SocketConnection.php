<?php

namespace Kraken\Transfer\Socket;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Transfer\TransferConnectionInterface;

class SocketConnection implements TransferConnectionInterface
{
    /**
     * @var SocketInterface
     */
    protected $conn;

    /**
     * @param SocketInterface $conn
     */
    public function __construct(SocketInterface $conn)
    {
        $this->conn = $conn;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getResourceId()
    {
        return $this->conn->getResourceId();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getEndpoint()
    {
        return $this->conn->getRemoteEndpoint();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAddress()
    {
        return $this->conn->getRemoteAddress();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getHost()
    {
        $address = explode(':', $this->getAddress());

        return $address[0];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPort()
    {
        $address = explode(':', $this->getAddress());

        return $address[1];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function send($data)
    {
        $this->conn->write($data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close()
    {
        $this->conn->close();
    }
}