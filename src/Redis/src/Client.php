<?php

namespace Kraken\Redis;

use Kraken\Throwable\Exception\LogicException;
use RuntimeException;
use Kraken\Loop\Loop;
use UnderflowException;
use Kraken\Promise\Promise;
use Kraken\Promise\Deferred;
use Kraken\Ipc\Socket\Socket;
use Kraken\Redis\Command\Enum;
use Kraken\Redis\Protocol\Resp;
use Kraken\Redis\Command\Builder;
use Kraken\Event\EventEmitter;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Promise\PromiseInterface;
use Clue\Redis\Protocol\Model\Request;
use Kraken\Event\EventEmitterInterface;
use Kraken\Redis\Command\CommandInterface;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Parser\ParserException;
use Closure;

class Client extends EventEmitter implements EventEmitterInterface,CommandInterface
{
    private $ending;
    private $closed;
    private $keys;
    private $cluster;
    private $connection;
    private $geo;
    private $hashed;
    private $hypeLogLog;
    private $lists;
    private $pubSub;
    private $scripting;
    private $server;
    private $sets;
    private $sortedSets;
    private $strings;
    private $transactions;

    public $serverVersion;
    /**
     * @var Socket
     */
    private $stream;
    /**
     * @var Resp
     */
    private $protocol;
    /**
     * @var Loop $loop
     */
    private $loop;
    protected $requests;

    /**
     * @overwrite
     * @param string $uri
     * @param LoopInterface $loop
     */
    public function __construct($uri, LoopInterface $loop)
    {
        parent::__construct($loop);
        $this->uri = $uri;
        $this->loop = $loop;
        $this->stream = null;
        $this->protocol = new Resp();
        $this->requests = [];
        $this->ending = false;
        $this->closed = false;
        $this->on('response',[$this, 'handleResponse']);
        $this->on('close', [$this, 'handleClose']);
        $this->on('disconnect',[$this, 'handleDisconnect']);
    }

    private function connection($uri)
    {
        try {
            $stream = new Socket($uri, $this->loop);

            return $stream;
        } catch (LogicException $e) {
            $this->end();
            $this->loop->stop();
        } catch (\Exception $e) {
            $this->end();
            $this->loop->stop();
        }

        return null;
    }

    private function dispatch(Request $command)
    {
        $request = new Deferred();
        $promise = $request->getPromise();
        if ($this->ending) {
            $request->reject(new RuntimeException('Connection closed'));
        } else {
            $this->stream->write($this->protocol->commands($command));
            $this->requests[] = $request;
        }

        return $promise;
    }

    protected function isBusy()
    {
        return empty($this->requests) ? false : true;
    }

    public function connect()
    {
        if ($this->stream !== null) {
            return;
        }

        $this->stream = $this->connection($this->uri);
        if ($this->stream->isOpen()) {
            $this->on('connect',[$this , 'handleConnect']);
        }

        //todo ; patch missing pub/sub,pipeline,auth
        $this->emit('connect', [$this]);
    }

    public function run(Closure $onConnect = null, Closure $onDisconnect = null)
    {
        if ($onConnect) {
            $this->on('connect', $onConnect);
        }

        if ($onDisconnect) {
            $this->on('disconnect', $onDisconnect);
        }

        $this->connect();
        $this->loop->start();
    }

    /**
     * @internal
     */
    public function handleConnect()
    {
        $protocol = $this->protocol;
        $that = $this;

        $this->stream->on('data', function($_, $chunk) use ($protocol, $that) {
            try {
                $models = $protocol->parseResponse($chunk);
            } catch (ParserException $error) {
                $this->ending = true;
                $that->emit('error', [$error]);
                $that->emit('close');

                return;
            }

            foreach ($models as $data) {
                try {
                    $this->emit('response', [$data]);
                } catch (UnderflowException $error) {
                    $this->ending = true;
                    $that->emit('error', [$error]);
                    $this->emit('close');
                }
            }
        });
    }

    /**
     * @internal
     */
    public function handleResponse(ModelInterface $message)
    {
        if (!$this->requests) {
            throw new UnderflowException('Unexpected reply received, no matching request found');
        }
        /* @var Deferred $request */
        $request = array_shift($this->requests);
        if ($message instanceof ErrorReply) {
            $request->reject($message);
        } else {
            $request->resolve($message->getValueNative());
        }
        if (!$this->isBusy()) {
            $this->emit('close');
        }
    }

    /**
     * @internal
     */
    public function handleDisconnect()
    {
        $this->removeListener('connect', [ $this, 'handleConnect' ]);
        $this->removeListener('disconnect', [ $this, 'handleDisconnect' ]);
        $this->removeListener('error', [ $this, 'handleError' ]);
        $this->removeListener('close', [ $this, 'handleClose']);
    }

    /**
     * @internal
     */
    public function handleClose()
    {
        if ($this->closed) {
            return;
        }
        $this->ending = true;
        $this->closed = true;
        $this->stream->close();
        $this->emit('disconnect');
        // reject all remaining requests in the queue
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Deferred */
            $request->reject(new RuntimeException('Connection closing'));
        }
        $this->loop->stop();
    }

    public function end()
    {
        if (!$this->ending) {
            $this->ending = true;
        }
    }

    /**
     * Commands ...
     */
    public function auth($password)
    {
        $command = Enum::AUTH;
        $args = [$password];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function append($key, $value)
    {
        $command = Enum::APPEND;
        $args = [$key, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function bgRewriteAoF()
    {
        $command = Enum::BGREWRITEAOF;

        return $this->dispatch(Builder::build($command));
    }

    public function bgSave()
    {
        $command = Enum::BGSAVE;

        return $this->dispatch(Builder::build($command));
    }

    public function bitCount($key, $start = 0, $end = 0)
    {
        $command = Enum::BITCOUNT;
        $args = [$key, $start, $end];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function bitField($key, $subCommand = null, ...$param)
    {
        $command = Enum::BITFIELD;
        switch ($subCommand = strtoupper($subCommand)) {
            case 'GET' : {
                list ($type, $offset) = $param;
                $args = [$subCommand, $type, $offset];
                break;
            }
            case 'SET' : {
                list ($type, $offset, $value) = $param;
                $args = [$subCommand, $type, $offset, $value];
                break;
            }
            case 'INCRBY' : {
                list ($type, $offset, $increment) = $param;
                $args = [$type, $offset, $increment];
                break;
            }
            case 'OVERFLOW' : {
                list ($behavior) = $param;
                $args = [$subCommand, $behavior];
                break;
            }
            default : {
                    $args = [];
                    break;
            }
        }
        $args = array_filter($args);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function bitOp($operation, $dstKey, $srcKey, ...$keys)
    {
        $command = Enum::BITOP;
        $args = [$operation, $dstKey, $srcKey];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function bitPos($key, $bit, $start = 0, $end = 0)
    {
        $command = Enum::BITPOS;
        $args = [$key, $bit, $start, $end];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function blPop(array $keys, $timeout)
    {
        // TODO: Implement blPop() method.
        $command = Enum::BLPOP;
        $keys[] = $timeout;
        $args = $keys;
        $promise = $this->dispatch(Builder::build($command, $args));
        $promise = $promise->then(function ($value) {
            if (is_array($value)) {
                list($k,$v) = $value;

                return [
                    'key'=>$k,
                    'value'=>$v
                ];
            }

            return $value;
        });

        return $promise;
    }

    public function brPop(array $keys, $timeout)
    {
        // TODO: Implement brPop() method.
        $command = Enum::BRPOP;
        $keys[] = $timeout;
        $args = $keys;
        $promise = $this->dispatch(Builder::build($command, $args));
        $promise = $promise->then(function ($value) {
            if (is_array($value)) {
                list($k,$v) = $value;

                return [
                    'key'=>$k,
                    'value'=>$v
                ];
            }

            return $value;
        });

        return $promise;
    }

    public function brPopLPush($src, $dst, $timeout)
    {
        // TODO: Implement brPopLPush() method.
        $command = Enum::BRPOPLPUSH;
        $args = [$src, $dst, $timeout];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function decr($key)
    {
        // TODO: Implement decr() method.
        $command = Enum::DECR;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function decrBy($key, $decrement)
    {
        // TODO: Implement decrBy() method.
        $command = Enum::DECRBY;
        $args = [$key, $decrement];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function del($key,...$keys)
    {
        $command = Enum::DEL;
        $keys[] = $key;
        $args = $keys;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function discard()
    {
        // TODO: Implement discard() method.
        $command = Enum::DISCARD;

        return $this->dispatch(Builder::build($command));
    }

    public function dump($key)
    {
        // TODO: Implement dump() method.
        $command = Enum::DUMP;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function exists($key, ...$keys)
    {
        // TODO: Implement exists() method.
        $command = Enum::EXISTS;
        $args = [$key];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function expire($key, $seconds)
    {
        // TODO: Implement expire() method.
        $command = Enum::EXPIRE;
        $args = [$key, $seconds];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function expireAt($key, $timestamp)
    {
        // TODO: Implement expireAt() method.
        $command = Enum::EXPIREAT;
        $args = [$key, $timestamp];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function get($key)
    {
        $command = Enum::GET;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function getBit($key, $offset)
    {
        // TODO: Implement getBit() method.
        $command = Enum::GETBIT;
        $args = [$key, $offset];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function getRange($key, $start, $end)
    {
        // TODO: Implement getRange() method.
        $command = Enum::GETRANGE;
        $args = [$key, $start, $end];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function getSet($key, $value)
    {
        // TODO: Implement getSet() method.
        $command = Enum::GETSET;
        $args = [$key, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function incr($key)
    {
        $command = Enum::INCR;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function incrBy($key, $increment)
    {
        // TODO: Implement incrBy() method.
        $command = Enum::INCRBY;
        $args = [$key, $increment];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function incrByFloat($key, $increment)
    {
        // TODO: Implement incrByFloat() method.
        $command = Enum::INCRBYFLOAT;
        $args = [$key, $increment];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function multi()
    {
        // TODO: Implement multi() method.
        $command = Enum::MULTI;

        return $this->dispatch(Builder::build($command));
    }

    public function persist($key)
    {
        $command = Enum::PERSIST;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pExpire($key, $milliseconds)
    {
        // TODO: Implement pExpire() method.
        $command = Enum::PEXPIRE;
        $args = [$key, $milliseconds];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pExpireAt($key, $milliseconds)
    {
        // TODO: Implement pExpireAt() method.
        $command = Enum::PEXPIREAT;
        $args = [$key, $milliseconds];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sync()
    {
        // TODO: Implement sync() method.
        $command = Enum::SYNC;

        return $this->dispatch(Builder::build($command));
    }

    public function time()
    {
        // TODO: Implement time() method.
        $command = Enum::TIME;

        return $this->dispatch(Builder::build($command));
    }

    public function touch($key, ...$keys)
    {
        $command = Enum::TOUCH;
        $args = [$key];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function ttl($key)
    {
        // TODO: Implement ttl() method.
        $command = Enum::TTL;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function type($key)
    {
        $command = Enum::TYPE;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function unLink($key, ...$keys)
    {
        $command = Enum::UNLINK;
        $args = [$key];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function unWatch()
    {
        // TODO: Implement unWatch() method.
        $command = Enum::UNWATCH;

        return $this->dispatch(Builder::build($command));
    }

    public function wait($numSlaves, $timeout)
    {
        // TODO: Implement wait() method.
        $command = Enum::WAIT;
        $args = [$numSlaves, $timeout];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function watch($key, ...$keys)
    {
        // TODO: Implement watch() method.
        $command = Enum::WATCH;
        $args = [$key];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function select($index)
    {
        // TODO: Implement select() method.
        $command = Enum::SELECT;

        return $this;
    }

    public function set($key, $value, array $options = [])
    {
        $command = Enum::SET;
        array_unshift($options, $key, $value);
        $args = $options;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function setBit($key, $offset, $value)
    {
        // TODO: Implement setBit() method.
        $command = Enum::SETBIT;
        $args = [$key, $offset, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function setEx($key, $seconds, $value)
    {
        $command = Enum::SETEX;
        $args = [$key, $seconds, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function setNx($key, $value)
    {
        $command = Enum::SETNX;
        $args = [$key, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function randomKey()
    {
        // TODO: Implement randomKey() method.
        $command = Enum::RANDOMKEY;

        return $this->dispatch(Builder::build($command));
    }

    public function readOnly()
    {
        // TODO: Implement readOnly() method.
        $command = Enum::READONLY;

        return $this->dispatch(Builder::build($command));
    }

    public function rename($key, $newKey)
    {
        $command = Enum::RENAME;
        $args = [$key, $newKey];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function renameNx($key, $newKey)
    {
        $command = Enum::RENAMENX;
        $args = [$key, $newKey];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function restore($key, $ttl, $value)
    {
        // TODO: Implement restore() method.
        $command = Enum::RESTORE;
        $args = [$key, $ttl, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function ping($message = 'PING')
    {
        $command = Enum::PING;
        $args = [$message];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function quit()
    {
        $command = Enum::QUIT;

        return $this->dispatch(Builder::build($command));
    }

    public function setRange($key, $offset, $value)
    {
        $command = Enum::SETRANGE;
        $args = [$key, $offset, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pTtl($key)
    {
        $command = Enum::PTTL;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pSetEx($key, $milliseconds, $value)
    {
        $command = Enum::PSETEX;
        $args = [$key, $milliseconds, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hDel($key, ...$fields)
    {
        $command = Enum::HDEL;
        $args = [$key];
        $args = array_merge($args, $fields);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hGet($key, $field)
    {
        $command = Enum::HGET;
        $args = [$key, $field];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hGetAll($key)
    {
        $command = Enum::HGETALL;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args))->then(function ($value) {
            if (!empty($value)) {
                $tmp = [];
                $size = count($value);
                for ($i=0; $i<$size; $i+=2) {
                    $field = $value[$i];
                    $val = $value[$i+1];
                    $tmp[$field] = $val;
                }
                $value = $tmp;
            }
        
            return $value;
        });
    }

    public function hIncrBy($key, $field, $increment)
    {
        $command = Enum::HINCRBY;
        $args = [$key, $field, $increment];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hIncrByFloat($key, $field, $increment)
    {
        $command = Enum::HINCRBYFLOAT;
        $args = [$key, $field, $increment];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hKeys($key)
    {
        $command = Enum::HKEYS;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hLen($key)
    {
        $command = Enum::HLEN;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hMGet($key, ...$fields)
    {
        $command = Enum::HMGET;
        $args = [$key];
        $args = array_merge($args, $fields);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hMSet($key, array $fvMap)
    {
        $command = Enum::HMSET;
        $args = [$key];
        if (!empty($fvMap)) {
            foreach ($fvMap as $field => $value) {
                $tmp[] = $field;
                $tmp[] = $value;
            }
            $fvMap = $tmp;        
        }
        $args = array_merge($args, $fvMap);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hSet($key, $field, $value)
    {
        $command = Enum::HSET;
        $args = [$key, $field, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hSetNx($key, $filed, $value)
    {
        $command = Enum::HSETNX;
        $args = [$key, $filed, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hStrLen($key, $field)
    {
        $command = Enum::HSTRLEN;
        $args = [$key, $field];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hVals($key)
    {
        $command = Enum::HVALS;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function geoAdd($key, array $coordinates)
    {
        // TODO: Implement geoAdd() method.
        $command = Enum::GEOADD;
        $args = [$key];
        $args = array_merge($args, $coordinates);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function geoHash($key, ...$members)
    {
        // TODO: Implement geoHash() method.
        $command = Enum::GEOHASH;
    }

    public function geoPos($key, ...$members)
    {
        // TODO: Implement geoPos() method.
        $command = Enum::GEOPOS;
        $args = [$key];
        $args = array_merge($args, $members);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function geoDist($key, $memberA, $memberB, $unit)
    {
        // TODO: Implement geoDist() method.
        $command = Enum::GEODIST;
        $args = [$key, $memberA, $memberB ,$unit];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function geoRadius($key, $longitude, $latitude, $unit, $command, $count, $sort)
    {
        // TODO: Implement geoRadius() method.
        $command = Enum::GEORADIUS;
        $args = [$key, $longitude, $latitude, $unit, $command, $count, $sort];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function geoRadiusByMember($key, $member, $unit, $command, $count, $sort, $store, $storeDist)
    {
        // TODO: Implement geoRadiusByMember() method.
        $command = Enum::GEORADIUSBYMEMBER;
        $args = [$key, $member, $unit, $command, $count, $sort, $store, $storeDist];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pSubscribe(...$patterns)
    {
        // TODO: Implement pSubscribe() method.
        $command = Enum::PSUBSCRIBE;
        $args = $patterns;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pubSub($command, array $args = [])
    {
        // TODO: Implement pubSub() method.
        $command = Enum::PUBSUB;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function publish($channel, $message)
    {
        // TODO: Implement publish() method.
        $command = Enum::PUBLISH;
        $args = [$channel, $message];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pUnsubscribe(...$patterns)
    {
        // TODO: Implement pUnsubscribe() method.
        $command = Enum::PUNSUBSCRIBE;
        $args = $patterns;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function unSubscribe(...$channels)
    {
        // TODO: Implement unSubscribe() method.
        $command = Enum::UNSUBSCRIBE;
        $args = $channels;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lIndex($key, $index)
    {
        // TODO: Implement lIndex() method.
        $command = Enum::LINDEX;
        $args = [$key, $index];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lInsert($key, $action, $pivot, $value)
    {
        // TODO: Implement lInsert() method.
        $command = Enum::LINSERT;
        $args = [$key, $action, $pivot, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lLen($key)
    {
        // TODO: Implement lLen() method.
        $command = Enum::LLEN;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lPop($key)
    {
        // TODO: Implement lPop() method.
        $command = Enum::LPOP;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lPush($key,...$values)
    {
        $command = Enum::LPUSH;
        array_unshift($values, $key);

        return $this->dispatch(Builder::build($command, $values));
    }

    public function lPushX($key, $value)
    {
        $command = Enum::LPUSHX;
        $args = [$key, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lRange($key, $start = 0, $stop = -1)
    {
        $command = Enum::LRANGE;
        $args = [$key, $start, $stop];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lRem($key, $count, $value)
    {
        // TODO: Implement lRem() method.
        $command = Enum::LREM;
        $args = [$key, $count, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lSet($key, $index, $value)
    {
        // TODO: Implement lSet() method.
        $command = Enum::LSET;
        $args = [$key, $index, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function lTrim($key, $start, $stop)
    {
        // TODO: Implement lTrim() method.
        $command = Enum::LTRIM;
        $args = [$key, $start, $stop];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function mGet($key, ...$values)
    {
        // TODO: Implement mGet() method.
        $command = Enum::MGET;
        $args = [$key];
        $args = array_merge($args, $values);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function mSet(array $kvMap)
    {
        // TODO: Implement mSet() method.
        $command = Enum::MSET;
        $args = $kvMap;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function monitor()
    {
        // TODO: Implement monitor() method.
        $command = Enum::MONITOR;

        return $this->dispatch(Builder::build($command));
    }

    public function move($key, $db)
    {
        // TODO: Implement move() method.
        $command = Enum::MOVE;
        $args = [$key, $db];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function mSetNx($kvMap)
    {
        // TODO: Implement mSetNx() method.
        $command = Enum::MSETNX;
        $args = $kvMap;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function rPop($key)
    {
        $command = Enum::RPOP;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function rPopLPush($src, $dst)
    {
        // TODO: Implement rPopLPush() method.
        $command = Enum::RPOPLPUSH;
        $args = [$src, $dst];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function rPush($key, ...$values)
    {
        $command = Enum::RPUSH;
        $args = [$key];
        $args = array_merge($args, $values);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function rPushX($key, $value)
    {
        $command = Enum::RPUSHX;
        $args = [$key, $value];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pFAdd($key, ...$elements)
    {
        // TODO: Implement pFAdd() method.
        $command = Enum::PFADD;
        $args = [$key];
        $args = array_merge($args, $elements);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pFCount(...$keys)
    {
        // TODO: Implement pFCount() method.
        $command = Enum::PFCOUNT;
        $args = $keys;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function pFMerge(array $dsKeyMap)
    {
        // TODO: Implement pFMerge() method.
        $command = Enum::PFMERGE;
        $args = $dsKeyMap;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterAddSlots(...$slots)
    {
        // TODO: Implement clusterAddSlots() method.
        $command = Enum::CLUSTER_ADDSLOTS;
        $args = $slots;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterCountFailureReports($nodeId)
    {
        // TODO: Implement clusterCountFailureReports() method.
        $command = Enum::CLUSTER_COUNT_FAILURE_REPORTS;
        $args = [$nodeId];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterCountKeysInSlot($slot)
    {
        // TODO: Implement clusterCountKeysInSlot() method.
        $command = Enum::CLUSTER_COUNTKEYSINSLOT;
        $args = $slot;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterDelSlots(...$slots)
    {
        // TODO: Implement clusterDelSlots() method.
        $command = Enum::CLUSTER_DELSLOTS;
        $args = $slots;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterFailOver($operation)
    {
        // TODO: Implement clusterFailOver() method.
    }

    public function clusterForget($nodeId)
    {
        // TODO: Implement clusterForget() method.
    }

    public function clusterGetKeyInSlot($slot, $count)
    {
        // TODO: Implement clusterGetKeyInSlot() method.
    }

    public function clusterInfo()
    {
        // TODO: Implement clusterInfo() method.
        $command = Enum::CLUSTER_INFO;

        return $this->dispatch(Builder::build($command));
    }

    public function clusterKeySlot($key)
    {
        // TODO: Implement clusterKeySlot() method.
        $command = Enum::CLUSTER_KEYSLOT;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterMeet($ip, $port)
    {
        // TODO: Implement clusterMeet() method.
        $command = Enum::CLUSTER_MEET;
        $args = [$ip, $port];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function clusterNodes()
    {
        // TODO: Implement clusterNodes() method.
    }

    public function clusterReplicate($nodeId)
    {
        // TODO: Implement clusterReplicate() method.
    }

    public function clusterReset($mode)
    {
        // TODO: Implement clusterReset() method.
    }

    public function clusterSaveConfig()
    {
        // TODO: Implement clusterSaveConfig() method.
    }

    /**
     * @inheritDoc
     */
    public function clusterSetConfigEpoch($configEpoch)
    {
        // TODO: Implement clusterSetConfigEpoch() method.
    }

    /**
     * @inheritDoc
     */
    public function clusterSetSlot($command, $nodeId)
    {
        // TODO: Implement clusterSetSlot() method.
        $command = Enum::CLUSTER_SETSLOT;
        $args = [$command, $nodeId];

        return $this->dispatch(Builder::build($command, $args));
    }

    /**
     * @inheritDoc
     */
    public function clusterSlaves($nodeId)
    {
        // TODO: Implement clusterSlaves() method.
        $command = Enum::CLUSTER_SLAVES;
        $args = [$nodeId];

        return $this->dispatch(Builder::build($command, $args));
    }

    /**
     * @inheritDoc
     */
    public function clusterSlots()
    {
        // TODO: Implement clusterSlots() method.
        $command = Enum::CLUSTER_SLOTS;

        return $this->dispatch(Builder::build($command));
    }

    public function flushAll()
    {
        $command = Enum::FLUSHALL;

        return $this->dispatch(Builder::build($command));
    }

    public function flushDb()
    {
        // TODO: Implement flushDb() method.
        $command = Enum::FLUSHDB;

        return $this->dispatch(Builder::build($command));
    }

    public function info($section = [])
    {
        $command = Enum::INFO;

        return $this->dispatch(Builder::build($command, $section))->then(function ($value) {
            if ($value) {
                $ret = explode(PHP_EOL, $value);
                $handled = [];
                $lastKey = '';
                foreach ($ret as $_ => $v) {
                    if (($pos = strpos($v, '#')) !== false) {
                        $lastKey = strtolower(substr($v,$pos+2));
                        $handled[$lastKey] = [];
                        continue;
                    }
                    $statMap = explode(':', $v);
                    if ($statMap[0]) {
                        list($name, $stat) = explode(':', $v);
                        $handled[$lastKey][$name] = $stat;
                    }
                }

                return $handled;
            }

            return $value;
        });
    }

    public function zAdd($key, array $options = [])
    {
        // TODO: Implement zAdd() method.
        $command = Enum::ZADD;
        $args = [$key];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zCard($key)
    {
        // TODO: Implement zCard() method.
        $command = Enum::ZCARD;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zCount($key, $min, $max)
    {
        // TODO: Implement zCount() method.
        $command = Enum::ZCOUNT;
        $args = [$key, $min, $max];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zIncrBy($key, $increment, $member)
    {
        // TODO: Implement zIncrBy() method.
        $command = Enum::ZINCRBY;
        $args = [$key, $increment, $member];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zInterStore($dst, $numKeys)
    {
        // TODO: Implement zInterStore() method.
        $command = Enum::ZINTERSTORE;
        $args = [$dst, $numKeys];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zLexCount($key, $min, $max)
    {
        // TODO: Implement zLexCount() method.
        $command = Enum::ZLEXCOUNT;
        $args = [$key, $min, $max];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRange($key, $star, $stop, array $options = [])
    {
        // TODO: Implement zRange() method.
        $command = Enum::ZRANGE;
        $args = [$key, $star,$stop];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRangeByLex($key, $min, $max, array $options = [])
    {
        // TODO: Implement zRangeByLex() method.
        $command = Enum::ZRANGEBYLEX;
        $args = [$key, $min, $max];
        $args = array_merge($args,$options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRevRangeByLex($key, $max, $min, array $options = [])
    {
        // TODO: Implement zRevRangeByLex() method.
        $command = Enum::ZREVRANGEBYLEX;
        $args = [$key, $max,$min];
        $args = array_merge($args,$options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRangeByScore($key, $min, $max, array $options = [])
    {
        // TODO: Implement zRangeByScore() method.
        $command = Enum::ZRANGEBYSCORE;
        $args = [$key, $min,$max];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRank($key, $member)
    {
        // TODO: Implement zRank() method.
        $command = Enum::ZRANK;
        $args = [$key,$member];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRem($key, ...$members)
    {
        // TODO: Implement zRem() method.
        $command = Enum::ZREM;
        $args = [$key];
        $args = array_merge($args, $members);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRemRangeByLex($key, $min, $max)
    {
        // TODO: Implement zRemRangeByLex() method.
        $command = Enum::ZREMRANGEBYLEX;
        $args = [$key, $min, $max];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRemRangeByRank($key, $start, $stop)
    {
        // TODO: Implement zRemRangeByRank() method.
        $command = Enum::ZREMRANGEBYRANK;
        $args = [$key, $start,$stop];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRemRangeByScore($key, $min, $max)
    {
        // TODO: Implement zRemRangeByScore() method.
        $command = Enum::ZREMRANGEBYSCORE;
        $args = [$key, $min, $max];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRevRange($key, $start, $stop, array $options = [])
    {
        // TODO: Implement zRevRange() method.
        $command = Enum::ZREVRANGE;
        $args = [$key, $start, $stop];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRevRangeByScore($key, $max, $min, array $options = [])
    {
        // TODO: Implement zRevRangeByScore() method.
        $command = Enum::ZREVRANGEBYSCORE;
        $args = [$key,$max,$min];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zRevRank($key, $member)
    {
        // TODO: Implement zRevRank() method.
        $command = Enum::ZREVRANK;
        $args = [$key,$member];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zScore($key, $member)
    {
        // TODO: Implement zScore() method.
        $command = Enum::ZSCORE;
        $args = [$key,$member];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function scan($cursor, array $options = [])
    {
        $command = Enum::SCAN;
        $args = [$cursor];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sScan($key, $cursor, array $options = [])
    {
        // TODO: Implement sScan() method.
        $command = Enum::SSCAN;
        $args = [$key, $cursor];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function hScan($key, $cursor, array $options = [])
    {
        // TODO: Implement hScan() method.
        $command = Enum::HSCAN;
        $args = [$key, $cursor];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function zScan($key, $cursor, array $options = [])
    {
        // TODO: Implement zScan() method.
        $command = Enum::ZSCAN;
        $args = [$key , $cursor];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sInter(...$keys)
    {
        // TODO: Implement sInter() method.
        $command = Enum::SINTER;
        $args = $keys;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sInterStore($dst, ...$keys)
    {
        // TODO: Implement sInterStore() method.
        $command = Enum::SINTERSTORE;
        $args = [$dst];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sIsMember($key, $member)
    {
        // TODO: Implement sIsMember() method.
        $command = Enum::SISMEMBER;
        $args = [$key ,$member];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function slaveOf($host, $port)
    {
        // TODO: Implement slaveOf() method.
        $command = Enum::SLAVEOF;
        $args = [$host, $port];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sLowLog($command, array $args = [])
    {
        // TODO: Implement sLowLog() method.
        $command = Enum::SLOWLOG;
        $args = array_merge([$command],$args);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sMembers($key)
    {
        // TODO: Implement sMembers() method.
        $command = Enum::SMEMBERS;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sMove($src, $dst, $members)
    {
        // TODO: Implement sMove() method.
        $command = Enum::SMOVE;
        $args = [$src, $dst];
        $args = array_merge( $args, $members);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sort($key, array $options = [])
    {
        // TODO: Implement sort() method.
        $command = Enum::SORT;
        $args = [$key];
        $args = array_merge($args, $options);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sPop($key, $count)
    {
        // TODO: Implement sPop() method.
        $command = Enum::SPOP;
        $args = [$key, $count];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sRandMember($key, $count)
    {
        // TODO: Implement sRandMember() method.
        $command = Enum::SRANDMEMBER;
        $args = [$key, $count];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sRem($key, ...$members)
    {
        // TODO: Implement sRem() method.
        $command = Enum::SREM;
        $args = [$key];
        $args = array_merge($args, $members);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function strLen($key)
    {
        // TODO: Implement strLen() method.
        $command = Enum::STRLEN;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function subscribe(...$channels)
    {
        // TODO: Implement subscribe() method.
        $command = Enum::SUBSCRIBE;
        $args = $channels;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sUnion(...$keys)
    {
        // TODO: Implement sUnion() method.
        $command = Enum::SUNION;
        $args = $keys;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sUnionStore($dst, ...$keys)
    {
        // TODO: Implement sUnionStore() method.
        $command = Enum::SUNIONSTORE;
        $args = [$dst];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sWapBb($opt, $dst, ...$keys)
    {
        // TODO: Implement sWapBb() method.
        $command = Enum::SWAPDB;
        $args = [$opt, $dst];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sAdd($key, ...$members)
    {
        // TODO: Implement sAdd() method.
        $command = Enum::SADD;
        $args = [$key];
        $args = array_merge($args, $members);

        return $this->dispatch(Builder::build($command, $args));
    }

    public function save()
    {
        // TODO: Implement save() method.
        $command = Enum::SAVE;

        return $this->dispatch(Builder::build($command));
    }

    public function sCard($key)
    {
        // TODO: Implement sCard() method.
        $command = Enum::SCARD;
        $args = [$key];

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sDiff(...$keys)
    {
        // TODO: Implement sDiff() method.
        $command = Enum::SDIFF;
        $args = $keys;

        return $this->dispatch(Builder::build($command, $args));
    }

    public function sDiffStore($dst, ...$keys)
    {
        // TODO: Implement sDiffStore() method.
        $command = Enum::SDIFFSTORE;
        $args = [$dst];
        $args = array_merge($args, $keys);

        return $this->dispatch(Builder::build($command, $args));
    }

    /**
     * @inheritDoc
     */
    public function hExists($key, $field)
    {
        // TODO: Implement hExists() method.
        $command = Enum::HEXISTS;
        $args = [$key, $field];

        return $this->dispatch(Builder::build($command, $args));
    }

    /**
     * @inheritDoc
     */
    public function readWrite()
    {
        // TODO: Implement readWrite() method.
        $command = Enum::READWRITE;

        return $this->dispatch(Builder::build($command));
    }

    /**
     * @inheritDoc
     */
    public function zUnionScore($dst, $numKeys)
    {
        // TODO: Implement zUnionScore() method.
        $command = Enum::ZUNIIONSCORE;
        $args = [$dst, $numKeys];

        return $this->dispatch(Builder::build($command, $args));
    }
};