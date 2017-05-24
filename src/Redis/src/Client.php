<?php

namespace Kraken\Redis;

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

class Client extends EventEmitter implements EventEmitterInterface,CommandInterface
{
    private $ending;
    private $closed;
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
        $this->uri = $uri;
        $this->loop = $loop;
        $this->stream = null;
        $this->protocol = new Resp();
        $this->requests = [];
        $this->ending = false;
        $this->closed = false;
        parent::__construct($loop);
    }

    public function connect()
    {
        if ($this->stream !== null) {
            return;
        }

        $this->stream = $this->connection($this->uri);
        if ($this->stream != null) {
            $this->on('connect',[$this , 'handleConnect']);
        }

        //todo ; patch missing pub/sub,pipeline,auth

        $this->emit('connect', [$this]);
    }

    public function handleDisconnect()
    {
        $this->removeListener('connect', [ $this, 'handleConnect' ]);
        $this->removeListener('disconnect', [ $this, 'handleDisconnect' ]);
        $this->removeListener('error', [ $this, 'handleError' ]);
        $this->removeListener('close', [ $this, 'handleClose']);
    }

    public function handleConnect()
    {
        $protocol = $this->protocol;
        $that = $this;
        $this->stream->on('data', function($socket, $chunk) use ($protocol, $that) {
            try {
                $models = $protocol->parseResponse($chunk);
            }
            catch (ParserException $error) {
                $that->emit('error', array($error));
                $this->ending = true;
                $that->emit('close');
                return;
            }

            foreach ($models as $data) {
                try {
                    $that->handleMessage($data);
                }
                catch (UnderflowException $error) {
                    $that->emit('error', array($error));
                    $this->ending = true;
                    $this->emit('close');
                    return;
                }
            }
        });

        $this->stream->on('close', [$this, 'handleClose']);
    }

    public function handleMessage(ModelInterface $message)
    {
        $this->on('close', [$this, 'handleClose']);
        $this->on('disconnect',[$this, 'handleDisconnect']);
        $this->stream->emit('data', [$this->stream, $message->getValueNative()]);
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
        if ($this->ending && !$this->isBusy()) {
            $this->emit('close');
        }
    }

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

    private function connection($uri)
    {
        $stream = new Socket($uri, $this->loop);

        return $stream;
    }

    public function dispatch(Request $command)
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

    public function end()
    {
        if (!$this->isBusy() && !$this->ending) {
            $this->ending = true;
        }
    }

    protected function isBusy()
    {
        return empty($this->requests) ? false : true;
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

    public function bitField($command, ...$param)
    {
        $command = Enum::BITFIELD;
    }

    public function bitOp($operation, $dstKey, ...$keys)
    {
        $command = Enum::BITOP;
    }

    public function bitPos($key, $bit, $start = 0, $end = 0)
    {
        $command = Enum::BITPOS;
    }

    public function blPop(array $keys, $timeout)
    {
        // TODO: Implement blPop() method.
        $command = Enum::BLPOP;

    }

    public function brPop(array $keys, $timeout)
    {
        // TODO: Implement brPop() method.
        $command = Enum::BRPOP;

    }

    public function brPopLPush($src, $dst, $timeout)
    {
        // TODO: Implement brPopLPush() method.
        $command = Enum::BRPOPLPUSH;

    }

    public function decr($key)
    {
        // TODO: Implement decr() method.
        $command = Enum::DECR;

    }

    public function decrBy($key, $decrement)
    {
        // TODO: Implement decrBy() method.
        $command = Enum::DECRBY;

    }

    public function discard()
    {
        // TODO: Implement discard() method.
        $command = Enum::DISCARD;

    }

    public function dump($key)
    {
        // TODO: Implement dump() method.
        $command = Enum::DUMP;

    }

    public function exists(...$keys)
    {
        // TODO: Implement exists() method.
        $command = Enum::EXISTS;

    }

    public function expire($key, $seconds)
    {
        // TODO: Implement expire() method.
        $command = Enum::EXPIRE;

    }

    public function expireAt($key, $timestamp)
    {
        // TODO: Implement expireAt() method.
        $command = Enum::EXPIREAT;

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

    }

    public function getRange($key, $start, $end)
    {
        // TODO: Implement getRange() method.
        $command = Enum::GETRANGE;

    }

    public function getSet($key, $value)
    {
        // TODO: Implement getSet() method.
        $command = Enum::GETSET;

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

    }

    public function incrByFloat($key, $increment)
    {
        // TODO: Implement incrByFloat() method.
        $command = Enum::INCRBYFLOAT;

    }

    public function multi()
    {
        // TODO: Implement multi() method.
        $command = Enum::MULTI;

    }

    public function persist($key)
    {
        // TODO: Implement persist() method.
        $command = Enum::PERSIST;

    }

    public function pExpire($key, $milliseconds)
    {
        // TODO: Implement pExpire() method.
        $command = Enum::PEXPIRE;

    }

    public function pExpireAt($key, $milliseconds)
    {
        // TODO: Implement pExpireAt() method.
        $command = Enum::PEXPIREAT;

    }

    public function sync()
    {
        // TODO: Implement sync() method.
        $command = Enum::SYNC;

    }

    public function time()
    {
        // TODO: Implement time() method.
        $command = Enum::TIME;

    }

    public function touch(...$keys)
    {
        // TODO: Implement touch() method.
        $command = Enum::TOUCH;

    }

    public function ttl($key)
    {
        // TODO: Implement ttl() method.
        $command = Enum::TTL;

    }

    public function type($key)
    {
        // TODO: Implement type() method.
        $command = Enum::TYPE;

    }

    public function unLink(...$keys)
    {
        // TODO: Implement unLink() method.
        $command = Enum::UNLINK;

    }

    public function unWatch()
    {
        // TODO: Implement unWatch() method.
        $command = Enum::UNWATCH;

    }

    public function wait($numSlaves, $timeout)
    {
        // TODO: Implement wait() method.
        $command = Enum::WAIT;

    }

    public function watch(...$keys)
    {
        // TODO: Implement watch() method.
        $command = Enum::WATCH;

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

    }

    public function setEx($key, $seconds, $value)
    {
        // TODO: Implement setEx() method.
        $command = Enum::SETEX;

    }

    public function setNx($key, $value)
    {
        // TODO: Implement setNx() method.
        $command = Enum::SETNX;

    }

    public function randomKey()
    {
        // TODO: Implement randomKey() method.
        $command = Enum::RANDOMKEY;

    }

    public function readOnly()
    {
        // TODO: Implement readOnly() method.
        $command = Enum::READONLY;

    }

    public function readWrtie()
    {
        // TODO: Implement readWrtie() method.
        $command = Enum::READWRITE;

    }

    public function rename($key, $newKey)
    {
        // TODO: Implement rename() method.
        $command = Enum::RENAME;

    }

    public function renameNx($key, $newKey)
    {
        // TODO: Implement renameNx() method.
        $command = Enum::RENAMENX;

    }

    public function restore($key, $ttl, $value)
    {
        // TODO: Implement restore() method.
        $command = Enum::RESTORE;

    }

    public function ping($message = 'PING')
    {
        // TODO: Implement ping() method.
        $command = Enum::PING;

    }

    public function quit()
    {
        // TODO: Implement quit() method.
        $command = Enum::QUIT;

    }

    public function setRange($key, $offset, $value)
    {
        //
        $command = Enum::SETRANGE;

    }

    public function pTtl($key)
    {
        $command = Enum::PTTL;

    }

    public function pSetEx($key, $milliseconds, $value)
    {
        $command = Enum::PSETEX;

    }

    public function hDel($key, ...$fields)
    {
        // TODO: Implement hDel() method.
    }

    public function hExsits($key, $field)
    {
        // TODO: Implement hExsits() method.
    }

    public function hGet($key, $field)
    {
        // TODO: Implement hGet() method.
    }

    public function hGetAll($key)
    {
        // TODO: Implement hGetAll() method.
    }

    public function hIncrBy($key, $field, $incrment)
    {
        // TODO: Implement hIncrBy() method.
    }

    public function hIncrByFloat($key, $field, $increment)
    {
        // TODO: Implement hIncrByFloat() method.
    }

    public function hKeys($key)
    {
        // TODO: Implement hKeys() method.
    }

    public function hLen($key)
    {
        // TODO: Implement hLen() method.
    }

    public function hMGet($key, ...$fields)
    {
        // TODO: Implement hMGet() method.
    }

    public function hMSet($key, array $fvMap)
    {
        // TODO: Implement hMSet() method.
    }

    public function hSet($key, $field, $value)
    {
        // TODO: Implement hSet() method.
    }

    public function hSetNx($key, $filed, $value)
    {
        // TODO: Implement hSetNx() method.
    }

    public function hStrLen($key, $field)
    {
        // TODO: Implement hStrLen() method.
    }

    public function hVals($key)
    {
        // TODO: Implement hVals() method.
    }

    public function geoAdd($key, array $coordinates)
    {
        // TODO: Implement geoAdd() method.
    }

    public function geoHash($key, ...$members)
    {
        // TODO: Implement geoHash() method.
    }

    public function geoPos($key, ...$members)
    {
        // TODO: Implement geoPos() method.
    }

    public function geoDist($key, $memberA, $memberB, $unit)
    {
        // TODO: Implement geoDist() method.
    }

    public function geoRadius($key, $longitude, $latitude, $unit, $command, $count, $sort)
    {
        // TODO: Implement geoRadius() method.
    }

    public function geoRadiusByMember($key, $member, $unit, $command, $count, $sort, $store, $storeDist)
    {
        // TODO: Implement geoRadiusByMember() method.
    }

    public function pSubscribe(...$patterns)
    {
        // TODO: Implement pSubscribe() method.
    }

    public function pubSub($command, array $args = [])
    {
        // TODO: Implement pubSub() method.
    }

    public function publish($channel, $message)
    {
        // TODO: Implement publish() method.
    }

    public function pUnsubscribe(...$patterns)
    {
        // TODO: Implement pUnsubscribe() method.
    }

    public function unSubscribe(...$channels)
    {
        // TODO: Implement unSubscribe() method.
    }

    public function lIndex($key, $index)
    {
        // TODO: Implement lIndex() method.
    }

    public function lInsert($key, $action, $pivot, $value)
    {
        // TODO: Implement lInsert() method.
    }

    public function lLen($key)
    {
        // TODO: Implement lLen() method.
    }

    public function lPop($key)
    {
        // TODO: Implement lPop() method.
    }

    public function lPush(array $kvMap)
    {
        // TODO: Implement lPush() method.
    }

    public function lPushX($key, $value)
    {
        // TODO: Implement lPushX() method.
    }

    public function lRange($key, $start, $stop)
    {
        // TODO: Implement lRange() method.
    }

    public function lRem($key, $count, $value)
    {
        // TODO: Implement lRem() method.
    }

    public function lSet($key, $index, $value)
    {
        // TODO: Implement lSet() method.
    }

    public function lTrim($key, $start, $stop)
    {
        // TODO: Implement lTrim() method.
    }

    public function mGet($key, ...$values)
    {
        // TODO: Implement mGet() method.
    }

    public function mSet(array $kvMap)
    {
        // TODO: Implement mSet() method.
    }

    public function monitor()
    {
        // TODO: Implement monitor() method.
    }

    public function move($key, $db)
    {
        // TODO: Implement move() method.
    }

    public function mSetNx($kvMap)
    {
        // TODO: Implement mSetNx() method.
    }

    public function rPop($key)
    {
        // TODO: Implement rPop() method.
    }

    public function rPopLPush($src, $dst)
    {
        // TODO: Implement rPopLPush() method.
    }

    public function rPush($key, ...$values)
    {
        // TODO: Implement rPush() method.
    }

    public function rPushX($key, $value)
    {
        // TODO: Implement rPushX() method.
    }

    public function pFAdd($key, ...$elements)
    {
        // TODO: Implement pFAdd() method.
    }

    public function pFCount(...$keys)
    {
        // TODO: Implement pFCount() method.
    }

    public function pFMerge(array $dsKeyMap)
    {
        // TODO: Implement pFMerge() method.
    }

    public function clientList()
    {
        // TODO: Implement clientList() method.
    }

    public function clientGetName()
    {
        // TODO: Implement clientGetName() method.
    }

    public function clientPause()
    {
        // TODO: Implement clientPause() method.
    }

    public function clientReply($operation)
    {
        // TODO: Implement clientReply() method.
    }

    public function clientSetName($connetionName)
    {
        // TODO: Implement clientSetName() method.
    }

    public function clusterAddSlots(...$slots)
    {
        // TODO: Implement clusterAddSlots() method.
    }

    public function clusterCountFailureReports($nodeId)
    {
        // TODO: Implement clusterCountFailureReports() method.
    }

    public function clusterCountKeysInSlot($slot)
    {
        // TODO: Implement clusterCountKeysInSlot() method.
    }

    public function clusterDelSlots(...$slots)
    {
        // TODO: Implement clusterDelSlots() method.
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
    }

    public function clusterKeySlot($key)
    {
        // TODO: Implement clusterKeySlot() method.
    }

    public function clusterMeet($ip, $port)
    {
        // TODO: Implement clusterMeet() method.
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
    }

    /**
     * @inheritDoc
     */
    public function clusterSlaves($nodeId)
    {
        // TODO: Implement clusterSlaves() method.
    }

    /**
     * @inheritDoc
     */
    public function clusterSlots()
    {
        // TODO: Implement clusterSlots() method.
    }

    public function flushAll($isAsync)
    {
        // TODO: Implement flushAll() method.
    }

    public function flushDb($isAsync)
    {
        // TODO: Implement flushDb() method.
    }

    public function zAdd($key, array $options = [])
    {
        // TODO: Implement zAdd() method.
    }

    public function zCard($key)
    {
        // TODO: Implement zCard() method.
    }

    public function zCount($key, $min, $max)
    {
        // TODO: Implement zCount() method.
    }

    public function zIncrBy($key, $increment, $member)
    {
        // TODO: Implement zIncrBy() method.
    }

    public function zInterStore($dst, $numKeys)
    {
        // TODO: Implement zInterStore() method.
    }

    public function zLexCount($key, $min, $max)
    {
        // TODO: Implement zLexCount() method.
    }

    public function zRange($key, $star, $stop, array $options = [])
    {
        // TODO: Implement zRange() method.
    }

    public function zRangeByLex($key, $min, $max, array $options = [])
    {
        // TODO: Implement zRangeByLex() method.
    }

    public function zRevRangeByLex($key, $max, $min, array $options = [])
    {
        // TODO: Implement zRevRangeByLex() method.
    }

    public function zRangeByScore($key, $min, $max, array $options = [])
    {
        // TODO: Implement zRangeByScore() method.
    }

    public function zRank($key, $member)
    {
        // TODO: Implement zRank() method.
    }

    public function zRem($key, ...$members)
    {
        // TODO: Implement zRem() method.
    }

    public function zRemRangeByLex($key, $min, $max)
    {
        // TODO: Implement zRemRangeByLex() method.
    }

    public function zRemRangeByRank($key, $start, $stop)
    {
        // TODO: Implement zRemRangeByRank() method.
    }

    public function zRemRangeByScore($key, $min, $max)
    {
        // TODO: Implement zRemRangeByScore() method.
    }

    public function zRevRange($key, $start, $stop, array $options = [])
    {
        // TODO: Implement zRevRange() method.
    }

    public function zRevRangeByScore($key, $max, $min, array $options = [])
    {
        // TODO: Implement zRevRangeByScore() method.
    }

    public function zRevRank($key, $member)
    {
        // TODO: Implement zRevRank() method.
    }

    public function zScore($key, $member)
    {
        // TODO: Implement zScore() method.
    }

    public function zUniionScore($dst, $numKeys)
    {
        // TODO: Implement zUniionScore() method.
    }

    public function scan($cursor, array $options = [])
    {
        // TODO: Implement scan() method.
    }

    public function sScan($key, $cursor, array $options = [])
    {
        // TODO: Implement sScan() method.
    }

    public function hScan($key, $cursor, array $options = [])
    {
        // TODO: Implement hScan() method.
    }

    public function zScan($key, $cursor, array $options = [])
    {
        // TODO: Implement zScan() method.
    }

    public function sInter(...$keys)
    {
        // TODO: Implement sInter() method.
    }

    public function sInterStore($dst, ...$keys)
    {
        // TODO: Implement sInterStore() method.
    }

    public function sIsMember($key, $member)
    {
        // TODO: Implement sIsMember() method.
    }

    public function slaveOf($host, $port)
    {
        // TODO: Implement slaveOf() method.
    }

    public function sLowLog($command, array $args = [])
    {
        // TODO: Implement sLowLog() method.
    }

    public function sMembers($key)
    {
        // TODO: Implement sMembers() method.
    }

    public function sMove($src, $dst, $members)
    {
        // TODO: Implement sMove() method.
    }

    public function sort($key, array $options = [])
    {
        // TODO: Implement sort() method.
    }

    public function sPop($key, $count)
    {
        // TODO: Implement sPop() method.
    }

    public function sRandMember($key, $count)
    {
        // TODO: Implement sRandMember() method.
    }

    public function sRem($key, ...$members)
    {
        // TODO: Implement sRem() method.
    }

    public function strLen($key)
    {
        // TODO: Implement strLen() method.
    }

    public function subscribe(...$channels)
    {
        // TODO: Implement subscribe() method.
    }

    public function sUnion(...$keys)
    {
        // TODO: Implement sUnion() method.
    }

    public function sUnionStore($dst, ...$keys)
    {
        // TODO: Implement sUnionStore() method.
    }

    public function sWapBb($opt, $dst, ...$keys)
    {
        // TODO: Implement sWapBb() method.
    }

    public function sAdd($key, ...$members)
    {
        // TODO: Implement sAdd() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function sCard($key)
    {
        // TODO: Implement sCard() method.
    }

    public function sDiff(...$keys)
    {
        // TODO: Implement sDiff() method.
    }

    public function sDiffStore($dst, ...$keys)
    {
        // TODO: Implement sDiffStore() method.
    }
};