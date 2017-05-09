<?php

namespace Kraken\Channel\Model\Socket\Connection;

use Kraken\Ipc\Socket\SocketInterface;

/**
 * @codeCoverageIgnore
 */
class Connection
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var SocketInterface
     */
    public $client;

    /**
     * @param string $id
     * @param SocketInterface $client
     */
    public function __construct($id, SocketInterface $client)
    {
        $this->id = $id;
        $this->client = $client;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->id);
        unset($this->client);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SocketInterface
     */
    public function getSocket()
    {
        return $this->client;
    }
}
