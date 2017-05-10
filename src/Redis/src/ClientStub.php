<?php

namespace Kraken\Redis;

use Kraken\Loop\Loop;
use Kraken\Ipc\Socket\Socket;
use Kraken\Promise\Promise;
use Kraken\Event\EventEmitter;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Redis\Command\Builder;
use Kraken\Redis\Protocol\Resp;
use Kraken\Promise\PromiseInterface;
use Kraken\Redis\Dispatcher\Dispatcher;
use Kraken\Redis\Protocol\Data\Request;
use Kraken\Promise\Deferred;
use UnderflowException;
use InvalidArgumentException;
use Clue\Redis\Protocol\Parser\ParserException;

abstract class ClientStub extends Dispatcher implements ClientStubInterface
{
    /**
     * @var Socket
     */
    private $stream;
    protected $requests;
    private $serializer;

    public function __construct($loop)
    {
        $this->resp = new Resp();
        $this->commandBuilder = new Builder();
        $this->requests = [];
        $this->serializer = $this->resp->getSerializer();
        parent::__construct($loop);
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

    public function create($uri)
    {
        $socket = new Socket($uri, $this->getLoop());
        $parser = $this->resp->getResponseParser();
        $serializer = $this->resp->getSerializer();
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

        $socket->on('close', [$that, 'close']);

        $promise = Promise::doResolve($that);

        $this->stream = $socket;

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
            $this->stream->write($command->serialized($this->serializer));
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
     * Commands
     */

    public function auth($password)
    {
        return $this->dispatch($this->commandBuilder->auth($password)->build());
    }

    public function append($key, $value)
    {
        return $this->dispatch($this->commandBuilder->append($key,$value)->build());
    }

    public function bgRewriteAoF()
    {
        // TODO: Implement bgRewriteAoF() method.
    }

    public function bgSave()
    {
        // TODO: Implement bgSave() method.
    }

    public function bitCount($key, $start = 0, $end = 0)
    {
        // TODO: Implement bitCount() method.
    }

    public function bitField($command, ...$param)
    {
        // TODO: Implement bitField() method.
    }

    public function bitOp($operation, $dstKey, ...$keys)
    {
        // TODO: Implement bitOp() method.
    }

    public function bitPos($key, $bit, $start = 0, $end = 0)
    {
        // TODO: Implement bitPos() method.
    }

    public function blPop(array $keys, $timeout)
    {
        // TODO: Implement blPop() method.
    }

    public function brPop(array $keys, $timeout)
    {
        // TODO: Implement brPop() method.
    }

    public function brPopLPush($src, $dst, $timeout)
    {
        // TODO: Implement brPopLPush() method.
    }

    public function decr($key)
    {
        // TODO: Implement decr() method.
    }

    public function decrBy($key, $decrement)
    {
        // TODO: Implement decrBy() method.
    }

    public function discard()
    {
        // TODO: Implement discard() method.
    }

    public function dump($key)
    {
        // TODO: Implement dump() method.
    }

    public function exists(...$keys)
    {
        // TODO: Implement exists() method.
    }

    public function expire($key, $seconds)
    {
        // TODO: Implement expire() method.
    }

    public function expireAt($key, $timestamp)
    {
        // TODO: Implement expireAt() method.
    }

    public function get($key)
    {
        return $this->dispatch($this->commandBuilder->get($key)->build());
    }

    public function getBit($key, $offset)
    {
        // TODO: Implement getBit() method.
    }

    public function getRange($key, $start, $end)
    {
        // TODO: Implement getRange() method.
    }

    public function getSet($key, $value)
    {
        // TODO: Implement getSet() method.
    }

    public function incr($key)
    {
        return $this->dispatch($this->commandBuilder->incr($key)->build());
    }

    public function incrBy($key, $increment)
    {
        // TODO: Implement incrBy() method.
    }

    public function incrByFloat($key, $increment)
    {
        // TODO: Implement incrByFloat() method.
    }

    public function multi()
    {
        // TODO: Implement multi() method.
    }

    public function persist($key)
    {
        // TODO: Implement persist() method.
    }

    public function pExpire($key, $milliseconds)
    {
        // TODO: Implement pExpire() method.
    }

    public function pExpireAt($key, $milliseconds)
    {
        // TODO: Implement pExpireAt() method.
    }

    public function sync()
    {
        // TODO: Implement sync() method.
    }

    public function time()
    {
        // TODO: Implement time() method.
    }

    public function touch(...$keys)
    {
        // TODO: Implement touch() method.
    }

    public function ttl($key)
    {
        // TODO: Implement ttl() method.
    }

    public function type($key)
    {
        // TODO: Implement type() method.
    }

    public function unLink(...$keys)
    {
        // TODO: Implement unLink() method.
    }

    public function unWatch()
    {
        // TODO: Implement unWatch() method.
    }

    public function wait($numSlaves, $timeout)
    {
        // TODO: Implement wait() method.
    }

    public function watch(...$keys)
    {
        // TODO: Implement watch() method.
    }

    public function select($index)
    {
        // TODO: Implement select() method.
    }

    public function set($key, $value, array $options = [])
    {
        return $this->dispatch($this->commandBuilder->set($key,$value,$options)->build());
    }

    public function setBit($key, $offset, $value)
    {
        // TODO: Implement setBit() method.
    }

    public function setEx($key, $seconds, $value)
    {
        // TODO: Implement setEx() method.
    }

    public function setNx($key, $value)
    {
        // TODO: Implement setNx() method.
    }

    public function randomKey()
    {
        // TODO: Implement randomKey() method.
    }

    public function readOnly()
    {
        // TODO: Implement readOnly() method.
    }

    public function readWrtie()
    {
        // TODO: Implement readWrtie() method.
    }

    public function rename($key, $newKey)
    {
        // TODO: Implement rename() method.
    }

    public function renameNx($key, $newKey)
    {
        // TODO: Implement renameNx() method.
    }

    public function restore($key, $ttl, $value)
    {
        // TODO: Implement restore() method.
    }

    public function ping($message = 'pong')
    {
        // TODO: Implement ping() method.
    }

    public function quit()
    {
        // TODO: Implement quit() method.
    }

    public function setRange($key, $offset, $value)
    {
        // TODO: Implement setRange() method.
    }

    public function pTtl($key)
    {
        // TODO: Implement pTtl() method.
    }

    public function pSetEx($key, $milliseconds, $value)
    {
        // TODO: Implement pSetEx() method.
    }
}