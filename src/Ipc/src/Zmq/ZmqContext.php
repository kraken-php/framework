<?php

namespace Kraken\Ipc\Zmq;

use Kraken\Loop\LoopInterface;
use ZMQ;
use ZMQContext as RawZMQContext;
use ZMQSocket as RawZMQSocket;

class ZmqContext
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ZMQContext
     */
    private $context;

    /**
     * @param LoopInterface $loop
     * @param ZMQContext|null $context
     */
    public function __construct(LoopInterface $loop, ZMQContext $context = null)
    {
        $this->loop = $loop;
        $this->context = $context ?: new RawZMQContext();
    }

    /**
     * @param string $method
     * @param mixed[] $args
     * @return ZmqSocket|mixed
     */
    public function __call($method, $args)
    {
        $res = call_user_func_array([ $this->context, $method ], $args);
        if ($res instanceof RawZMQSocket)
        {
            $res = $this->wrapSocket($res);
        }
        return $res;
    }

    /**
     * @param RawZMQSocket $socket
     * @return ZmqSocket
     */
    private function wrapSocket(RawZMQSocket $socket)
    {
        $wrapped = new ZmqSocket($socket, $this->loop);

        if ($this->isReadableSocketType($socket->getSocketType()))
        {
            $wrapped->attachReadListener();
        }

        return $wrapped;
    }

    /**
     * @param $type
     * @return bool
     */
    private function isReadableSocketType($type)
    {
        $readableTypes = array(
            ZMQ::SOCKET_PULL,
            ZMQ::SOCKET_SUB,
            ZMQ::SOCKET_REQ,
            ZMQ::SOCKET_REP,
            ZMQ::SOCKET_ROUTER,
            ZMQ::SOCKET_DEALER,
        );

        return in_array($type, $readableTypes);
    }
}
