<?php

namespace Kraken\Network\Websocket\Driver\Version\RFC6455;

use Kraken\Network\NetworkConnectionInterface;
use Ratchet\WebSocket\Version\DataInterface;
use Ratchet\WebSocket\Version\RFC6455\Frame;

class Connection implements NetworkConnectionInterface
{
    /**
     * @var NetworkConnectionInterface
     */
    protected $connection;

    /**
     * @param NetworkConnectionInterface $connection
     */
    public function __construct(NetworkConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->connection);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->connection->$name = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->connection->$name;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->connection->$name);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->connection->$name);
    }

    /**
     * Return decorated connection.
     *
     * @return NetworkConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getResourceId()
    {
        return $this->connection->getResourceId();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getEndpoint()
    {
        return $this->connection->getEndpoint();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAddress()
    {
        return $this->connection->getAddress();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->connection->getHost();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->connection->getPort();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function send($msg)
    {
        if (!$this->WebSocket->closing)
        {
            if (!($msg instanceof DataInterface))
            {
                $msg = new Frame($msg);
            }

            $this->connection->send($msg->getContents());
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close($code = 1000)
    {
        if ($this->WebSocket->closing)
        {
            return;
        }

        if ($code instanceof DataInterface)
        {
            $this->send($code);
        }
        else
        {
            $this->send(new Frame(pack('n', $code), true, Frame::OP_CLOSE));
        }

        $this->connection->close();

        $this->WebSocket->closing = true;
    }
}
