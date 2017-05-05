<?php

namespace Kraken\Redis;

use Kraken\Event\EventEmitter;
use Kraken\Stream\AsyncStream;
use Kraken\Promise\Deferred;
use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Parser\ParserException;
use Clue\Redis\Protocol\Serializer\SerializerInterface;
use Clue\Redis\Protocol\Factory as ProtocolFactory;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\StatusReply;
use UnderflowException;
use RuntimeException;
use InvalidArgumentException;

class AsyncRedisClient extends EventEmitter implements Client
{
    private $stream;
    private $parser;
    private $serializer;
    private $requests = array();
    private $ending = false;
    private $closed = false;

    private $subscribed = 0;
    private $psubscribed = 0;
    private $monitoring = false;

    public function __construct(AsyncStream $stream, ParserInterface $parser = null, SerializerInterface $serializer = null)
    {
        parent::__construct($stream->getLoop());

        if ($parser === null || $serializer === null) {
            $factory = new ProtocolFactory();
            if ($parser === null) {
                $parser = $factory->createResponseParser();
            }
            if ($serializer === null) {
                $serializer = $factory->createSerializer();
            }
        }

        $that = $this;
        $stream->on('data', function($chunk) use ($parser, $that) {
            try {
                $models = $parser->pushIncoming($chunk);
            }
            catch (ParserException $error) {
                $that->emit('error', array($error));
                $that->close();
                return;
            }

            foreach ($models as $data) {
                try {
                    $that->handleMessage($data);
                }
                catch (UnderflowException $error) {
                    $that->emit('error', array($error));
                    $that->close();
                    return;
                }
            }
        });

        $stream->on('close', array($this, 'close'));

        $this->stream = $stream;
        $this->parser = $parser;
        $this->serializer = $serializer;
    }

    public function __call($name, $args)
    {
        $request = new Deferred();
        $promise = $request->getPromise();

        $name = strtolower($name);

        // special (p)(un)subscribe commands only accept a single parameter and have custom response logic applied
        static $pubsubs = array('subscribe', 'unsubscribe', 'psubscribe', 'punsubscribe');

        if ($this->ending) {
            $request->reject(new RuntimeException('Connection closed'));
        } elseif (count($args) !== 1 && in_array($name, $pubsubs)) {
            $request->reject(new InvalidArgumentException('PubSub commands limited to single argument'));
        } else {
            $this->stream->write($this->serializer->getRequestMessage($name, $args));
            $this->requests []= $request;
        }

        if ($name === 'monitor') {
            $monitoring =& $this->monitoring;
            $promise->then(function () use (&$monitoring) {
                $monitoring = true;
            });
        } elseif (in_array($name, $pubsubs)) {
            $that = $this;
            $subscribed =& $this->subscribed;
            $psubscribed =& $this->psubscribed;

            $promise->then(function ($array) use ($that, &$subscribed, &$psubscribed) {
                $first = array_shift($array);

                // (p)(un)subscribe messages are to be forwarded
                $that->emit($first, $array);

                // remember number of (p)subscribe topics
                if ($first === 'subscribe' || $first === 'unsubscribe') {
                    $subscribed = $array[1];
                } else {
                    $psubscribed = $array[1];
                }
            });
        }

        return $promise;
    }

    public function handleMessage(ModelInterface $message)
    {
        $this->emit('data', array($message));

        if ($this->monitoring && $this->isMonitorMessage($message)) {
            $this->emit('monitor', array($message));
            return;
        }

        if (($this->subscribed !== 0 || $this->psubscribed !== 0) && $message instanceof MultiBulkReply) {
            $array = $message->getValueNative();
            $first = array_shift($array);

            // pub/sub messages are to be forwarded and should not be processed as request responses
            if (in_array($first, array('message', 'pmessage'))) {
                $this->emit($first, $array);
                return;
            }
        }

        if (!$this->requests) {
            throw new UnderflowException('Unexpected reply received, no matching request found');
        }

        $request = array_shift($this->requests);
        /* @var $request Deferred */

        if ($message instanceof ErrorReply) {
            $request->reject($message);
        } else {
            $request->resolve($message->getValueNative());
        }

        if ($this->ending && !$this->isBusy()) {
            $this->close();
        }
    }

    public function isBusy()
    {
        return !!$this->requests;
    }

    public function end()
    {
        $this->ending = true;

        if (!$this->isBusy()) {
            $this->close();
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }

        $this->ending = true;
        $this->closed = true;

        $this->stream->close();

        $this->emit('close');

        // reject all remaining requests in the queue
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var Deferred $request */
            $request->reject(new RuntimeException('Connection closing'));
        }
    }

    private function isMonitorMessage(ModelInterface $message)
    {
        // Check status '1409172115.207170 [0 127.0.0.1:58567] "ping"' contains otherwise uncommon '] "'
        return ($message instanceof StatusReply && strpos($message->getValueNative(), '] "') !== false);
    }
}