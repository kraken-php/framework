<?php

namespace Kraken\Io\Socket;

use Kraken\Io\ServerConnectionInterface;
use Kraken\Ipc\Socket\SocketInterface;

class SocketConnection implements ServerConnectionInterface
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
     */
    function send($data)
    {
        $this->conn->write($data);
    }

    /**
     * @override
     */
    function close()
    {
        $this->conn->close();
    }
}