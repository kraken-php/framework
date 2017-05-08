<?php

namespace Kraken\Channel\Model\Zmq\Buffer;

use Kraken\Ipc\Zmq\ZmqSocket;

/**
 * @codeCoverageIgnore
 */
class Buffer
{
    /**
     * @var ZmqSocket
     */
    protected $socket;

    /**
     * @var string[]
     */
    protected $messageBuffer;

    /**
     * @var int
     */
    protected $messageBufferSize;

    /**
     * @var int
     */
    protected $messageBufferMax;

    /**
     * @param ZmqSocket $socket
     * @param int $bufferSize
     */
    public function __construct(ZmqSocket $socket, $bufferSize = 0)
    {
        $this->socket = $socket;
        $this->messageBuffer = [];
        $this->messageBufferSize = 0;
        $this->messageBufferMax = $bufferSize;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->erase();
        unset($this->socket);
    }

    /**
     * @param string[] $frame
     * @return bool
     */
    public function add($frame)
    {
        if ($this->messageBufferSize >= $this->messageBufferMax && $this->messageBufferMax > 0)
        {
            return false;
        }

        $this->messageBuffer[] = $frame;
        $this->messageBufferSize++;

        return true;
    }

    /**
     *
     */
    public function send()
    {
        foreach ($this->messageBuffer as $message)
        {
            $this->socket->send($message);
        }

        $this->messageBuffer = [];
        $this->messageBufferSize = 0;
    }

    /**
     *
     */
    public function erase()
    {
        $this->messageBuffer = [];
        $this->messageBufferSize = 0;
    }
}
