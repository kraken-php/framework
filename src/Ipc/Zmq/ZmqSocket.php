<?php

namespace Kraken\Ipc\Zmq;

use Kraken\Event\BaseEventEmitter;
use Kraken\Loop\LoopInterface;
use ZMQ;
use ZMQSocket as RawZMQSocket;

class ZmqSocket extends BaseEventEmitter
{
    /**
     * @var resource
     */
    public $fd;

    /**
     * @var bool
     */
    public $closed = false;

    /**
     * @var RawZMQSocket
     */
    private $socket;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ZmqBuffer
     */
    private $buffer;

    /**
     * @param RawZMQSocket $socket
     * @param LoopInterface $loop
     */
    public function __construct(RawZMQSocket $socket, LoopInterface $loop)
    {
        $this->socket = $socket;
        $this->loop = $loop;

        $this->fd = $this->socket->getSockOpt(ZMQ::SOCKOPT_FD);

        $writeListener = [ $this, 'handleEvent' ];
        $this->buffer = new ZmqBuffer($socket, $this->fd, $this->loop, $writeListener);
    }

    /**
     * Attach read listener.
     */
    public function attachReadListener()
    {
        $this->loop->addReadStream($this->fd, [ $this, 'handleEvent' ]);
    }

    /**
     * Handle ZMQ Event.
     */
    public function handleEvent()
    {
        while ($this->socket !== null)
        {
            $events = $this->socket->getSockOpt(ZMQ::SOCKOPT_EVENTS);

            $hasEvents = ($events & ZMQ::POLL_IN) || ($events & ZMQ::POLL_OUT && $this->buffer->listening);
            if (!$hasEvents)
            {
                break;
            }

            if ($events & ZMQ::POLL_IN)
            {
                $this->handleReadEvent();
            }

            if ($events & ZMQ::POLL_OUT && $this->buffer->listening)
            {
                $this->buffer->handleWriteEvent();
            }
        }
    }

    /**
     * Handle ZMQ Read Event.
     */
    public function handleReadEvent()
    {
        $messages = $this->socket->recvmulti(ZMQ::MODE_DONTWAIT);
        if (false !== $messages)
        {
            $this->emit('messages', [ $messages ]);
        }
    }

    /**
     * Return socket.
     *
     * @return RawZMQSocket
     */
    public function getWrappedSocket()
    {
        return $this->socket;
    }

    /**
     * Subscribe socket to channel.
     *
     * @param mixed $channel
     */
    public function subscribe($channel)
    {
        $this->socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $channel);
    }

    /**
     * Unsubscribe socket from channel.
     *
     * @param mixed $channel
     */
    public function unsubscribe($channel)
    {
        $this->socket->setSockOpt(ZMQ::SOCKOPT_UNSUBSCRIBE, $channel);
    }

    /**
     * Send message.
     *
     * @param string $message
     */
    public function send($message)
    {
        $this->buffer->send($message);
    }

    /**
     * Close connection and discard not sent data.
     */
    public function close()
    {
        if ($this->closed)
        {
            return;
        }

        $this->emit('end', [ $this ]);
        $this->loop->removeStream($this->fd);
        $this->buffer->flushListeners();
        $this->flushListeners();
        unset($this->socket);
        $this->closed = true;
    }

    /**
     * Deactivate socket, wait to complete sending all unfinished data, then close connection.
     */
    public function end()
    {
        if ($this->closed)
        {
            return;
        }

        $that = $this;
        $this->buffer->on('end', function() use($that) {
            $that->close();
        });

        $this->buffer->end();
    }

    /**
     * @param string $method
     * @param mixed[] $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([ $this->socket, $method ], $args);
    }
}
