<?php

namespace Kraken\Redis;

use Kraken\Loop\Loop;
use Kraken\Ipc\Socket\Socket;
use Kraken\Promise\Promise;
use Kraken\Event\EventEmitter;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Redis\Command\Traits\Foundation;
use Kraken\Redis\Dispatcher\DispatcherInterface;
use Kraken\Redis\Protocol\Resp;
use Kraken\Promise\PromiseInterface;
use Kraken\Redis\Dispatcher\Dispatcher;
use Kraken\Redis\Protocol\Data\Request;
use Kraken\Promise\Deferred;
use UnderflowException;
use InvalidArgumentException;
use Clue\Redis\Protocol\Parser\ParserException;

abstract class ClientStub extends Dispatcher implements ClientStubInterface,DispatcherInterface
{
    /**
     * @var Socket
     */
    private $stream;
    protected $requests;
    private $protocol;

    use Foundation;

    public function __construct($loop)
    {
        $this->protocol = new Resp();
        $this->requests = [];
        parent::__construct($loop);
    }

    public function create($uri)
    {
        $this->stream = new Socket($uri, $this->getLoop());
        $parts = $this->parseUrl($uri);
        $protocol = $this->protocol;
        $that = $this;

        $this->stream->on('data', function($socket, $chunk) use ($protocol, $that) {
            try {
                $models = $protocol->parseResponse($chunk);
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

        $this->stream->on('close', [$that, 'close']);

        $promise = Promise::doResolve($that);

//        if (isset($parts['auth'])) {
//            $promise = $promise->then(function (ClientStub $client) use ($parts) {
//                $command = $this->commandBuilder->auth($parts['auth'])->build();
//                return $client->dispatch($command)->then(
//                    function () use ($client) {
//                        return $client;
//                    },
//                    function ($error) use ($client) {
//                        $client->emit('close');
//                        throw $error;
//                    }
//                );
//            });
//        }
//
//        if (isset($parts['db'])) {
//            $promise = $promise->then(function (ClientStub $client) use ($parts) {
//                $command = $this->commandBuilder->select($parts['db'])->build();
//                return $client->dispatch($command)->then(
//                    function () use ($client) {
//                        return $client;
//                    },
//                    function ($error) use ($client) {
//                        $client->emit('close');
//                        throw $error;
//                    }
//                );
//            });
//        }

        return $promise;
    }

    public function dispatch(Request $command)
    {
        $request = new Deferred();
        $promise = $request->getPromise();
        $name = strtolower($command->getCommand());

        // special (p)(un)subscribe commands only accept a single parameter and have custom response logic applied
        static $pubsubs = array('subscribe', 'unsubscribe', 'psubscribe', 'punsubscribe');

        if (count($command->getArgs()) !== 1 && in_array($name, $pubsubs)) {
            $request->reject(new InvalidArgumentException('PubSub commands limited to single argument'));
        } else {
            $this->stream->write($this->protocol->commands($command));
            $this->requests[]= $request;
        }

//        if ($name === 'monitor') {
//            $monitoring =& $this->monitoring;
//            $promise->then(function () use (&$monitoring) {
//                $monitoring = true;
//            });
//        } elseif (in_array($name, $pubsubs)) {
//            $that = $this;
//            $subscribed =& $this->subscribed;
//            $psubscribed =& $this->psubscribed;
//
//            $promise->then(function ($array) use ($that, &$subscribed, &$psubscribed) {
//                $first = array_shift($array);
//
//                // (p)(un)subscribe messages are to be forwarded
//                $that->emit($first, $array);
//
//                // remember number of (p)subscribe topics
//                if ($first === 'subscribe' || $first === 'unsubscribe') {
//                    $subscribed = $array[1];
//                } else {
//                    $psubscribed = $array[1];
//                }
//            });
//        }

        return $promise;
    }

    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param string|null $target
     * @return array with keys host, port, auth and db
     * @throws InvalidArgumentException
     */
    protected function parseUrl($target)
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
}