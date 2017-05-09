<?php

namespace Kraken\Redis;

use Kraken\Loop\Loop;
use Kraken\Ipc\Socket\Socket;
use Kraken\Promise\Promise;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Promise\PromiseInterface;

use Evenement\EventEmitter;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\StatusReply;
use Clue\Redis\Protocol\Factory as ProtocolFactory;
use Clue\Redis\Protocol\Serializer\SerializerInterface;


use UnderflowException;
use RuntimeException;
use InvalidArgumentException;
use React\Promise\Deferred;
use Clue\Redis\Protocol\Parser\ParserException;

/**
 * @package Kraken\Redis
 *
 * @see https://redis.io/commands
 * @method PromiseInterface set(string $key, string $value)
 * @method PromiseInterface append(string $key, string $value)
 * @method PromiseInterface get(string $key)
 * @method PromiseInterface incr(string $key)
 */

class Client extends EventEmitter implements ClientInterface
{
    protected $loop;
    protected static $protocol;
    /**
     * @var Socket
     */
    private $stream;
    private $parser;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    private $requests = array();
    private $ending = false;
    private $closed = false;
    private $subscribed = 0;
    private $psubscribed = 0;
    private $monitoring = false;

    /**
     * @overwrite
     * @param \React\Stream\Stream $uri
     * @param LoopInterface $loop
     */
    public function __construct($uri, LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->uri = $uri;
        self::$protocol = new ProtocolFactory();
    }

    public function connect()
    {
        $uri = $this->uri;
        $socket = new Socket($uri, $this->loop);
        $parser = self::$protocol->createResponseParser();
        $serializer = self::$protocol->createSerializer();
        $parts = $this->parseUrl($uri);
        $that = $this;
        $this->stream = $socket;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $socket->on('data', function($socket, $chunk) use ($parser, $that) {
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

        $socket->on('close', array($this, 'close'));

        $promise = Promise::doResolve($that);

        if (isset($parts['auth'])) {
            $promise = $promise->then(function (Client $client) use ($parts) {
                return $client->auth($parts['auth'])->then(
                    function () use ($client) {
                        return $client;
                    },
                    function ($error) use ($client) {
                        $client->close();
                        throw $error;
                    }
                );
            });
        }

        if (isset($parts['db'])) {
            $promise = $promise->then(function (StreamingClient $client) use ($parts) {
                return $client->select($parts['db'])->then(
                    function () use ($client) {
                        return $client;
                    },
                    function ($error) use ($client) {
                        $client->close();
                        throw $error;
                    }
                );
            });
        }

        return $promise;
    }

    /**
     * @param string|null $target
     * @return array with keys host, port, auth and db
     * @throws InvalidArgumentException
     */
    private function parseUrl($target)
    {
        if ($target === null) {
            $target = 'tcp://127.0.0.1';
        }
        if (strpos($target, '://') === false) {
            $target = 'tcp://' . $target;
        }

        $parts = parse_url($target);
        if ($parts === false || !isset($parts['host']) || $parts['scheme'] !== 'tcp') {
            throw new InvalidArgumentException('Given URL can not be parsed');
        }

        if (!isset($parts['port'])) {
            $parts['port'] = 6379;
        }

        if ($parts['host'] === 'localhost') {
            $parts['host'] = '127.0.0.1';
        }

        $auth = null;
        if (isset($parts['user'])) {
            $auth = $parts['user'];
        }
        if (isset($parts['pass'])) {
            $auth .= ':' . $parts['pass'];
        }
        if ($auth !== null) {
            $parts['auth'] = $auth;
        }

        if (isset($parts['path']) && $parts['path'] !== '') {
            // skip first slash
            $parts['db'] = substr($parts['path'], 1);
        }

        unset($parts['scheme'], $parts['user'], $parts['pass'], $parts['path']);

        return $parts;
    }

    public function __call($name, $args)
    {
        $request = new Deferred();
        $promise = $request->promise();

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
            /* @var $request Deferred */
            $request->reject(new RuntimeException('Connection closing'));
        }
        /**
         * @var Loop $loop
         */
        $loop  = $this->stream->getLoop();
        $loop->stop();
    }

    private function isMonitorMessage(ModelInterface $message)
    {
        // Check status '1409172115.207170 [0 127.0.0.1:58567] "ping"' contains otherwise uncommon '] "'
        return ($message instanceof StatusReply && strpos($message->getValueNative(), '] "') !== false);
    }
};