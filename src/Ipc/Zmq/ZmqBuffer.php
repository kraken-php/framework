<?php

namespace Kraken\Ipc\Zmq;

use Kraken\Event\BaseEventEmitter;
use Kraken\Loop\LoopInterface;
use ZMQ;
use ZMQSocket as RawZMQSocket;
use ZMQSocketException;

class ZmqBuffer extends BaseEventEmitter
{
    /**
     * @var RawZMQSocket
     */
    public $socket;

    /**
     * @var bool
     */
    public $closed = false;

    /**
     * @var bool
     */
    public $listening = false;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var resource
     */
    private $fd;

    /**
     * @var callable
     */
    private $writeListener;

    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * @param RawZMQSocket $socket
     * @param resource $fd
     * @param LoopInterface $loop
     * @param $writeListener
     */
    public function __construct(RawZMQSocket $socket, $fd, LoopInterface $loop, $writeListener)
    {
        $this->socket = $socket;
        $this->fd = $fd;
        $this->loop = $loop;
        $this->writeListener = $writeListener;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function send($message)
    {
        if ($this->closed)
        {
            return false;
        }

        $this->messages[] = $message;

        if (!$this->listening)
        {
            $this->listening = true;
            $this->loop->addWriteStream($this->fd, $this->writeListener);
        }

        return true;
    }

    /**
     *
     */
    public function end()
    {
        $this->closed = true;

        if (!$this->listening)
        {
            $this->emit('end');
        }
    }

    /**
     *
     */
    public function handleWriteEvent()
    {
        foreach ($this->messages as $i=>$message)
        {
            try
            {
                $message = (array) $message;
                $sent = (bool) $this->socket->sendmulti($message, ZMQ::MODE_DONTWAIT);
//                if ($sent)
//                {
                    unset($this->messages[$i]);
                    if (0 === count($this->messages))
                    {
                        $this->loop->removeWriteStream($this->fd);
                        $this->listening = false;
                        $this->emit('end');
                    }
//                }
            }
            catch (ZMQSocketException $ex)
            {
                $this->emit('error', [ $ex ]);
            }
        }
    }
}
