<?php

namespace Kraken\Redis\Command;

interface CommandInterface
{
    /**
     * @doc https://redis.io/commands/auth
     * @since 1.0.0
     * @param $password
     * @return mixed
     */
    public function auth($password);
    /**
     * @doc https://redis.io/commands/append
     * @since 2.0.0
     * @param $key
     * @param $value
     * @return mixed
     */
    public function append($key, $value);

    /**
     * @doc https://redis.io/commands/bgrewriteaof
     * @since 1.0.0
     * @return mixed
     */
    public function bgRewriteAoF();

    /**
     * @doc https://redis.io/commands/bgsave
     * @since 1.0.0
     * @return mixed
     */
    public function bgSave();

    /**
     * @doc https://redis.io/commands/bitcount
     * @since 2.6.0
     * @param $key
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public function bitCount($key, $start=0, $end=0);

    /**
     * @doc https://redis.io/commands/bitfield
     * @since 3.2.0
     * @param $command
     * @param $key
     * @param ...$param
     * @return mixed
     */
    public function bitField($command, $key, ...$param);

    /**
     * @doc
     * @param $operation
     * @param $dstKey
     * @param $srcKey
     * @param ...$keys
     * @return mixed
     */
    public function bitOp($operation,$dstKey,$srcKey, ...$keys);

    /**
     * @doc https://redis.io/commands/bitpos
     * @since 2.8.7
     * @param $key
     * @param $bit
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public function bitPos($key,$bit,$start=0,$end=0);

    /**
     * @doc https://redis.io/commands/blpop
     * @since 2.0.0
     * @param array $keys
     * @param $timeout
     * @return mixed
     */
    public function blPop(array $keys, $timeout);

    /**
     * @doc https://redis.io/commands/brpop
     * @since 2.0.0
     * @param array $keys
     * @param $timeout
     * @return mixed
     */
    public function brPop(array $keys,$timeout);

    /**
     * @doc https://redis.io/commands/brpoplpush
     * @since 2.2.0
     * @param $src
     * @param $dst
     * @param $timeout
     * @return mixed
     */
    public function brPopLPush($src,$dst,$timeout);

    /**
     * @doc https://redis.io/commands/decr
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function decr($key);

    /**
     * @doc https://redis.io/commands/decrby
     * @since 1.0.0
     * @param $key
     * @param $decrement
     * @return mixed
     */
    public function decrBy($key,$decrement);

    /**
     * @doc https://redis.io/commands/del
     * @since 1.0.0
     * @param $key
     * @param ...$keys
     * @return mixed
     */
    public function del($key,...$keys);

    /**
     * @doc https://redis.io/commands/discard
     * @since 2.0.0
     * @return mixed
     */
    public function discard();

    /**
     * @doc https://redis.io/commands/dump
     * @since 2.6.0
     * @param $key
     * @return mixed
     */
    public function dump($key);

    /**
     * @doc https://redis.io/commands/exists
     * @since 1.0.0
     * @param $key
     * @param ...$keys
     * @return mixed
     */
    public function exists($key, ...$keys);

    /**
     * @doc https://redis.io/commands/expire
     * @since 1.0.0
     * @param $key
     * @param $seconds
     * @return mixed
     */
    public function expire($key,$seconds);

    /**
     * @doc https://redis.io/commands/expireat
     * @since 1.2.0
     * @param $key
     * @param $timestamp
     * @return mixed
     */
    public function expireAt($key,$timestamp);

    /**
     * @doc https://redis.io/commands/get
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @doc https://redis.io/commands/getbit
     * @since 2.2.0
     * @param $key
     * @param $offset
     * @return mixed
     */
    public function getBit($key,$offset);

    /**
     * @doc https://redis.io/commands/getrange
     * @since 2.4.0
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getRange($key,$start,$end);

    /**
     * @doc https://redis.io/commands/getset
     * @since 1.0.0
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getSet($key,$value);

    /**
     * @doc https://redis.io/commands/hdel
     * @since 2.0.0
     * @param $key
     * @param ...$fields
     * @return mixed
     */
    public function hDel($key,...$fields);

    /**
     * @doc https://redis.io/commands/hexists
     * @since 2.0.0
     * @param $key
     * @param $field
     * @return mixed
     */
    public function hExists($key,$field);

    /**
     * @doc https://redis.io/commands/hget
     * @since 2.0.0
     * @param $key
     * @param $field
     * @return mixed
     */
    public function hGet($key,$field);

    /**
     * @doc https://redis.io/commands/hgetall
     * @since 2.0.0
     * @param $key
     * @return mixed
     */
    public function hGetAll($key);

    /**
     * @doc https://redis.io/commands/hincrby
     * @since 2.0.0
     * @param $key
     * @param $field
     * @param $incrment
     * @return mixed
     */
    public function hIncrBy($key,$field,$incrment);

    /**
     * @doc https://redis.io/commands/hincrbyfloat
     * @since 2.6.0
     * @param $key
     * @param $field
     * @param $increment
     * @return mixed
     */
    public function hIncrByFloat($key,$field,$increment);

    /**
     * @doc https://redis.io/commands/hkeys
     * @since 2.0.0
     * @param $key
     * @return mixed
     */
    public function hKeys($key);

    /**
     * @doc https://redis.io/commands/hlen
     * @since 2.0.0
     * @param $key
     * @return mixed
     */
    public function hLen($key);

    /**
     * @doc https://redis.io/commands/hmget
     * @since 2.0.0
     * @param $key
     * @param ...$fields
     * @return mixed
     */
    public function hMGet($key,...$fields);

    /**
     * @doc https://redis.io/commands/hmset
     * @since 2.0.0
     * @param $key
     * @param array $fvMap
     * @return mixed
     */
    public function hMSet($key,array $fvMap);

    /**
     * @doc https://redis.io/commands/hset
     * @since 2.0.0
     * @param $key
     * @param $field
     * @param $value
     * @return mixed
     */
    public function hSet($key,$field,$value);

    /**
     * @doc https://redis.io/commands/hsetnx
     * @since 2.0.0
     * @param $key
     * @param $filed
     * @param $value
     * @return mixed
     */
    public function hSetNx($key,$filed,$value);

    /**
     * @doc https://redis.io/commands/hstrlen
     * @since 3.2.0
     * @param $key
     * @param $field
     * @return mixed
     */
    public function hStrLen($key,$field);

    /**
     * @doc https://redis.io/commands/hvals
     * @since 2.0.0
     * @param $key
     * @return mixed
     */
    public function hVals($key);

    /**
     * @doc https://redis.io/commands/incr
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function incr($key);

    /**
     * @doc https://redis.io/commands/incrby
     * @since 1.0.0
     * @param $key
     * @param $increment
     * @return mixed
     */
    public function incrBy($key,$increment);

    /**
     * @doc https://redis.io/commands/incrbyfloat
     * @since 2.6.0
     * @param $key
     * @param $increment
     * @return mixed
     */
    public function incrByFloat($key,$increment);

    /**
     * @doc https://redis.io/commands/multi
     * @since 1.2.0
     * @return mixed
     */
    public function multi();

    /**
     * @doc https://redis.io/commands/persist
     * @since 2.2.0
     * @param $key
     * @return mixed
     */
    public function persist($key);

    /**
     * @doc https://redis.io/commands/pexpire
     * @since 2.6.0
     * @param $key
     * @param $milliseconds
     * @return mixed
     */
    public function pExpire($key,$milliseconds);

    /**
     * @doc https://redis.io/commands/pexpireat
     * @since 2.6.0
     * @param $key
     * @param $milliseconds
     * @return mixed
     */
    public function pExpireAt($key,$milliseconds);

    /**
     * @doc https://redis.io/commands/sync
     * @since 1.0.0
     * @return mixed
     */
    public function sync();

    /**
     * @doc https://redis.io/commands/time
     * @since 2.6.0
     * @return mixed
     */
    public function time();

    /**
     * @doc https://redis.io/commands/touch
     * @since 3.2.1
     * @param $key
     * @param ...$keys
     * @return mixed
     */
    public function touch($key, ...$keys);

    /**
     * @doc https://redis.io/commands/ttl
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function ttl($key);

    /**
     * @doc https://redis.io/commands/type
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function type($key);

    /**
     * @doc https://redis.io/commands/unlink
     * @since 4.0.0
     * @param $key
     * @param ...$keys
     * @return mixed
     */
    public function unLink($key, ...$keys);

    /**
     * @doc https://redis.io/commands/unwatch
     * @since 2.2.0
     * @return mixed
     */
    public function unWatch();

    /**
     * @doc https://redis.io/commands/wait
     * @since 3.0.0
     * @param $numSlaves
     * @param $timeout
     * @return mixed
     */
    public function wait($numSlaves,$timeout);

    /**
     * @doc https://redis.io/commands/watch
     * @since 2.2.0
     * @param $key
     * @param ...$keys
     * @return mixed
     */
    public function watch($key, ...$keys);

    /**
     * @doc https://redis.io/commands/select
     * @since 1.0.0
     * @param $index
     * @return mixed
     */
    public function select($index);

    /**
     * @doc https://redis.io/commands/set
     * @since 1.0.0
     * @param $key
     * @param $value
     * @param array $options
     * @return mixed
     */
    public function set($key,$value,array $options);

    /**
     * @doc https://redis.io/commands/setbit
     * @since 2.2.0
     * @param $key
     * @param $offset
     * @param $value
     * @return mixed
     */
    public function setBit($key,$offset,$value);

    /**
     * @doc https://redis.io/commands/setex
     * @since 2.0.0
     * @param $key
     * @param $seconds
     * @param $value
     * @return mixed
     */
    public function setEx($key,$seconds,$value);

    /**
     * @doc https://redis.io/commands/setnx
     * @since 1.0.0
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setNx($key,$value);

    /**
     * @doc https://redis.io/commands/randomkey
     * @since 1.0.0
     * @return mixed
     */
    public function randomKey();

    /**
     * @doc https://redis.io/commands/readonly
     * @since 3.0.0
     * @return mixed
     */
    public function readOnly();

    /**
     * @doc https://redis.io/commands/readwrite
     * @since 3.0.0
     * @return mixed
     */
    public function readWrite();

    /**
     * @doc https://redis.io/commands/rename
     * @since 1.0.0
     * @param $key
     * @param $newKey
     * @return mixed
     */
    public function rename($key,$newKey);

    /**
     * @doc https://redis.io/commands/renamenx
     * @since 1.0.0
     * @param $key
     * @param $newKey
     * @return mixed
     */
    public function renameNx($key,$newKey);

    /**
     * @doc https://redis.io/commands/restore
     * @since 2.6.0
     * @param $key
     * @param $ttl
     * @param $value
     * @return mixed
     */
    public function restore($key,$ttl,$value);

    /**
     * @doc https://redis.io/commands/ping
     * @since 1.0.0
     * @param string $message
     * @return mixed
     */
    public function ping($message='pong');

    /**
     * @doc https://redis.io/commands/quit
     * @since 1.0.0
     * @return mixed
     */
    public function quit();

    /**
     * @doc https://redis.io/commands/setrange
     * @since 2.2.0
     * @param $key
     * @param $offset
     * @param $value
     * @return mixed
     */
    public function setRange($key,$offset,$value);

    /**
     * @doc https://redis.io/commands/geoadd
     * @since 3.2.0
     * @param $key
     * @param array $coordinates
     * @return mixed
     */
    public function geoAdd($key,array $coordinates);

    /**
     * @doc https://redis.io/commands/geohash
     * @since 3.2.0
     * @param $key
     * @param ...$members
     * @return mixed
     */
    public function geoHash($key,...$members);

    /**
     * @doc https://redis.io/commands/geopos
     * @since 3.2.0
     * @param $key
     * @param ...$members
     * @return mixed
     */
    public function geoPos($key,...$members);

    /**
     * @doc https://redis.io/commands/geodist
     * @since 3.2.0
     * @param $key
     * @param $memberA
     * @param $memberB
     * @param $unit
     * @return mixed
     */
    public function geoDist($key,$memberA,$memberB,$unit);

    /**
     * @doc https://redis.io/commands/georadius
     * @since 3.2.0
     * @param $key
     * @param $longitude
     * @param $latitude
     * @param $unit
     * @param $command
     * @param $count
     * @param $sort
     * @return mixed
     */
    public function geoRadius($key,$longitude,$latitude,$unit,$command,$count,$sort);

    /**
     * @doc https://redis.io/commands/georadiusbymember
     * @since 3.2.0
     * @param $key
     * @param $member
     * @param $unit
     * @param $command
     * @param $count
     * @param $sort
     * @param $store
     * @param $storeDist
     * @return mixed
     */
    public function geoRadiusByMember($key,$member,$unit,$command,$count,$sort,$store,$storeDist);

    /**
     * @doc https://redis.io/commands/pttl
     * @since 2.6.0
     * @param $key
     * @return mixed
     */
    public function pTtl($key);

    /**
     * @doc https://redis.io/commands/psetex
     * @since 2.6.0
     * @param $key
     * @param $milliseconds
     * @param $value
     * @return mixed
     */
    public function pSetEx($key,$milliseconds,$value);

    /**
     * @doc https://redis.io/commands/psubscribe
     * @since 2.0.0
     * @param ...$patterns
     * @return mixed
     */
    public function pSubscribe(...$patterns);

    /**
     * @doc https://redis.io/commands/pubsub
     * @since 2.8.0
     * @param $command
     * @param array $args
     * @return mixed
     */
    public function pubSub($command,array $args = []);

    /**
     * @doc https://redis.io/commands/publish
     * @since 2.0.0
     * @param $channel
     * @param $message
     * @return mixed
     */
    public function publish($channel,$message);

    /**
     * @doc https://redis.io/commands/punsubscribe
     * @since 2.0.0
     * @param ...$patterns
     * @return mixed
     */
    public function pUnsubscribe(...$patterns);

    /**
     * @doc https://redis.io/commands/unsubscribe
     * @since 2.0.0
     * @param ...$channels
     * @return mixed
     */
    public function unSubscribe(...$channels);

    /**
     * @doc https://redis.io/commands/lindex
     * @since 1.0.0
     * @param $key
     * @param $index
     * @return mixed
     */
    public function lIndex($key,$index);

    /**
     * @doc https://redis.io/commands/linsert
     * @since 2.2.0
     * @param $key
     * @param $action
     * @param $pivot
     * @param $value
     * @return mixed
     */
    public function lInsert($key,$action,$pivot,$value);

    /**
     * @doc https://redis.io/commands/llen
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function lLen($key);

    /**
     * @doc https://redis.io/commands/lpop
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function lPop($key);

    /**
     * @doc https://redis.io/commands/lpush
     * @since 1.0.0
     * @param array $kvMap
     * @return mixed
     */
    public function lPush(array $kvMap);

    /**
     * @doc https://redis.io/commands/lpushx
     * @since 2.2.0
     * @param $key
     * @param $value
     * @return mixed
     */
    public function lPushX($key,$value);

    /**
     * @doc https://redis.io/commands/lrange
     * @since 1.0.0
     * @param $key
     * @param $start
     * @param $stop
     * @return mixed
     */
    public function lRange($key,$start,$stop);

    /**
     * @doc https://redis.io/commands/lrem
     * @since 1.0.0
     * @param $key
     * @param $count
     * @param $value
     * @return mixed
     */
    public function lRem($key,$count,$value);

    /**
     * @doc https://redis.io/commands/lset
     * @since 1.0.0
     * @param $key
     * @param $index
     * @param $value
     * @return mixed
     */
    public function lSet($key,$index,$value);

    /**
     * @doc https://redis.io/commands/ltrim
     * @since 1.0.0
     * @param $key
     * @param $start
     * @param $stop
     * @return mixed
     */
    public function lTrim($key,$start,$stop);

    /**
     * @doc https://redis.io/commands/mget
     * @since 1.0.0
     * @param $key
     * @param ...$values
     * @return mixed
     */
    public function mGet($key,...$values);

    /**
     * @doc https://redis.io/commands/mset
     * @since 1.0.1
     * @param array $kvMap
     * @return mixed
     */
    public function mSet(array $kvMap);

    /**
     * @doc https://redis.io/commands/monitor
     * @since 1.0.0
     * @return mixed
     */
    public function monitor();

    /**
     * @doc https://redis.io/commands/move
     * @since 1.0.0
     * @param $key
     * @param $db
     * @return mixed
     */
    public function move($key,$db);

    /**
     * @doc https://redis.io/commands/msetnx
     * @since 1.0.1
     * @param $kvMap
     * @return mixed
     */
    public function mSetNx($kvMap);

    /**
     * @doc https://redis.io/commands/rpop
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function rPop($key);

    /**
     * @doc https://redis.io/commands/rpoplpush
     * @since 1.2.0
     * @param $src
     * @param $dst
     * @return mixed
     */
    public function rPopLPush($src,$dst);

    /**
     * @doc https://redis.io/commands/rpush
     * @since 1.0.0
     * @param $key
     * @param ...$values
     * @return mixed
     */
    public function rPush($key,...$values);

    /**
     * @doc https://redis.io/commands/rpushx
     * @since 2.2.0
     * @param $key
     * @param $value
     * @return mixed
     */
    public function rPushX($key,$value);

    /**
     * @doc https://redis.io/commands/pfadd
     * @since 2.8.9
     * @param $key
     * @param ...$elements
     * @return mixed
     */
    public function pFAdd($key,...$elements);

    /**
     * @doc https://redis.io/commands/pfcount
     * @since 2.8.9
     * @param ...$keys
     * @return mixed
     */
    public function pFCount(...$keys);

    /**
     * @doc https://redis.io/commands/pfmerge
     * @since 2.8.9
     * @param array $dsKeyMap
     * @return mixed
     */
    public function pFMerge(array $dsKeyMap);

//    public function clientList();
//    public function clientGetName();
//    public function clientPause();
//    public function clientReply($operation);
//    public function clientSetName($connetionName);

    /**
     * @doc https://redis.io/commands/cluster-addslots
     * @sinc 3.0.0
     * @param ...$slots
     * @return mixed
     */
    public function clusterAddSlots(...$slots);

    /**
     * @doc https://redis.io/commands/cluster-count-failure-reports
     * @since 3.0.0
     * @param $nodeId
     * @return mixed
     */
    public function clusterCountFailureReports($nodeId);

    /**
     * @doc https://redis.io/commands/cluster-countkeysinslot
     * @since 3.0.0
     * @param $slot
     * @return mixed
     */
    public function clusterCountKeysInSlot($slot);

    /**
     * @doc https://redis.io/commands/cluster-delslots
     * @since 3.0.0
     * @param ...$slots
     * @return mixed
     */
    public function clusterDelSlots(...$slots);

    /**
     * @doc https://redis.io/commands/cluster-failover
     * @since 3.0.0
     * @param $operation
     * @return mixed
     */
    public function clusterFailOver($operation);

    /**
     * @doc https://redis.io/commands/cluster-forget
     * @since 3.0.0
     * @param $nodeId
     * @return mixed
     */
    public function clusterForget($nodeId);

    /**
     * @doc https://redis.io/commands/cluster-getkeysinslot
     * @since 3.0.0
     * @param $slot
     * @param $count
     * @return mixed
     */
    public function clusterGetKeyInSlot($slot,$count);

    /**
     * @doc https://redis.io/commands/cluster-info
     * @since 3.0.0
     * @return mixed
     */
    public function clusterInfo();

    /**
     * @doc https://redis.io/commands/cluster-keyslot
     * @since 3.0.0
     * @param $key
     * @return mixed
     */
    public function clusterKeySlot($key);

    /**
     * @doc https://redis.io/commands/cluster-meet
     * @since 3.0.0
     * @param $ip
     * @param $port
     * @return mixed
     */
    public function clusterMeet($ip,$port);

    /**
     * @doc https://redis.io/commands/cluster-nodes
     * @since 3.0.0
     * @return mixed
     */
    public function clusterNodes();

    /**
     * @doc https://redis.io/commands/cluster-replicate
     * @since 3.0.0
     * @param $nodeId
     * @return mixed
     */
    public function clusterReplicate($nodeId);

    /**
     * @doc https://redis.io/commands/cluster-reset
     * @since 3.0.0
     * @param $mode
     * @return mixed
     */
    public function clusterReset($mode);

    /**
     * @doc https://redis.io/commands/cluster-saveconfig
     * @since 3.0.0
     * @return mixed
     */
    public function clusterSaveConfig();

    /**
     * @doc https://redis.io/commands/cluster-set-config-epoch
     * @since 3.0.0
     * @param $configEpoch
     * @return mixed
     */
    public function clusterSetConfigEpoch($configEpoch);

    /**
     * @doc https://redis.io/commands/cluster-setslot
     * @since 3.0.0
     * @param $command
     * @param $nodeId
     * @return mixed
     */
    public function clusterSetSlot($command,$nodeId);

    /**
     * @doc https://redis.io/commands/cluster-slaves
     * @since 3.0.0
     * @param $nodeId
     * @return mixed
     */
    public function clusterSlaves($nodeId);

    /**
     * @doc https://redis.io/commands/cluster-slots
     * @since 3.0.0
     * @return mixed
     */
    public function clusterSlots();

    /**
     * @doc https://redis.io/commands/flushall
     * @since 1.0.0
     * @return mixed
     */
    public function flushAll();

    /**
     * @doc https://redis.io/commands/flushdb
     * @since 1.0.0
     * @return mixed
     */
    public function flushDb();

    /**
     * @doc https://redis.io/commands/info
     * @since 1.0.0
     * @param array $section
     * @return mixed
     */
    public function info($section = []);

    /**
     * @doc https://redis.io/commands/zadd
     * @since 1.2.0
     * @param $key
     * @param array $options
     * @return mixed
     */
    public function zAdd($key,array $options = []);

    /**
     * @doc https://redis.io/commands/zcard
     * @since 1.2.0
     * @param $key
     * @return mixed
     */
    public function zCard($key);

    /**
     * @doc https://redis.io/commands/zcount
     * @since 2.0.0
     * @param $key
     * @param $min
     * @param $max
     * @return mixed
     */
    public function zCount($key,$min,$max);

    /**
     * @doc https://redis.io/commands/zincrby
     * @since 1.2.0
     * @param $key
     * @param $increment
     * @param $member
     * @return mixed
     */
    public function zIncrBy($key,$increment,$member);

    /**
     * @doc https://redis.io/commands/zinterstore
     * @since 2.0.0
     * @param $dst
     * @param $numKeys
     * @return mixed
     */
    public function zInterStore($dst,$numKeys);

    /**
     * @doc https://redis.io/commands/zlexcount
     * @since 2.8.9
     * @param $key
     * @param $min
     * @param $max
     * @return mixed
     */
    public function zLexCount($key,$min,$max);

    /**
     * @doc https://redis.io/commands/zrange
     * @since 1.2.0
     * @param $key
     * @param $star
     * @param $stop
     * @param array $options
     * @return mixed
     */
    public function zRange($key,$star,$stop,array $options = []);

    /**
     * @doc https://redis.io/commands/zrangebylex
     * @since 2.8.9
     * @param $key
     * @param $min
     * @param $max
     * @param array $options
     * @return mixed
     */
    public function zRangeByLex($key,$min,$max,array $options = []);

    /**
     * @doc https://redis.io/commands/zrevrangebylex
     * @since 2.8.9
     * @param $key
     * @param $max
     * @param $min
     * @param array $options
     * @return mixed
     */
    public function zRevRangeByLex($key,$max,$min,array $options = []);

    /**
     * @doc https://redis.io/commands/zrevrangebyscore
     * @since 2.2.0
     * @param $key
     * @param $min
     * @param $max
     * @param array $options
     * @return mixed
     */
    public function zRangeByScore($key,$min,$max,array $options = []);

    /**
     * @doc https://redis.io/commands/zrank
     * @since 2.0.0
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRank($key,$member);

    /**
     * @doc https://redis.io/commands/zrem
     * @since 1.2.0
     * @param $key
     * @param ...$members
     * @return mixed
     */
    public function zRem($key,...$members);

    /**
     * @doc https://redis.io/commands/zremrangebylex
     * @since 2.8.9
     * @param $key
     * @param $min
     * @param $max
     * @return mixed
     */
    public function zRemRangeByLex($key,$min,$max);

    /**
     * @doc https://redis.io/commands/zremrangebyrank
     * @since 2.0.0
     * @param $key
     * @param $start
     * @param $stop
     * @return mixed
     */
    public function zRemRangeByRank($key, $start, $stop);

    /**
     * @doc https://redis.io/commands/zremrangebyscore
     * @since 1.2.0
     * @param $key
     * @param $min
     * @param $max
     * @return mixed
     */
    public function zRemRangeByScore($key, $min, $max);

    /**
     * @doc https://redis.io/commands/zrevrange
     * @since 1.2.0
     * @param $key
     * @param $start
     * @param $stop
     * @param array $options
     * @return mixed
     */
    public function zRevRange($key,$start,$stop,array $options = []);

    /**
     * @doc https://redis.io/commands/zrevrangebyscore
     * @since 2.2.0
     * @param $key
     * @param $max
     * @param $min
     * @param array $options
     * @return mixed
     */
    public function zRevRangeByScore($key,$max,$min,array $options = []);

    /**
     * @doc https://redis.io/commands/zrevrank
     * @since 2.0.0
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRevRank($key,$member);

    /**
     * @doc https://redis.io/commands/zscore
     * @since 1.2.0
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zScore($key,$member);

    /**
     * @doc https://redis.io/commands/zunionstore
     * @since 2.0.0
     * @param $dst
     * @param $numKeys
     * @return mixed
     */
    public function zUnionScore($dst,$numKeys);

    /**
     * @doc https://redis.io/commands/scan
     * @since 2.8.0
     * @param $cursor
     * @param array $options
     * @return mixed
     */
    public function scan($cursor,array $options = []);

    /**
     * @doc https://redis.io/commands/sscan
     * @since 2.8.0
     * @param $key
     * @param $cursor
     * @param array $options
     * @return mixed
     */
    public function sScan($key,$cursor,array $options = []);

    /**
     * @doc https://redis.io/commands/hscan
     * @since 2.8.0
     * @param $key
     * @param $cursor
     * @param array $options
     * @return mixed
     */
    public function hScan($key,$cursor,array $options = []);

    /**
     * @doc https://redis.io/commands/zscan
     * @since 2.8.0
     * @param $key
     * @param $cursor
     * @param array $options
     * @return mixed
     */
    public function zScan($key,$cursor,array $options = []);

    /**
     * @doc https://redis.io/commands/sinter
     * @since 1.0.0
     * @param ...$keys
     * @return mixed
     */
    public function sInter(...$keys);

    /**
     * @doc https://redis.io/commands/sinterstore
     * @since 1.0.0
     * @param $dst
     * @param ...$keys
     * @return mixed
     */
    public function sInterStore($dst,...$keys);

    /**
     * @doc https://redis.io/commands/sismember
     * @since 1.0.0
     * @param $key
     * @param $member
     * @return mixed
     */
    public function sIsMember($key,$member);

    /**
     * @doc https://redis.io/commands/slaveof
     * @since 1.0.0
     * @param $host
     * @param $port
     * @return mixed
     */
    public function slaveOf($host,$port);

    /**
     * @doc https://redis.io/commands/slowlog
     * @since 2.2.12
     * @param $command
     * @param array $args
     * @return mixed
     */
    public function sLowLog($command,array $args=[]);

    /**
     * @doc https://redis.io/commands/smembers
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function sMembers($key);

    /**
     * @doc https://redis.io/commands/smove
     * @since 1.0.0
     * @param $src
     * @param $dst
     * @param $members
     * @return mixed
     */
    public function sMove($src,$dst,$members);

    /**
     * @doc https://redis.io/commands/sort
     * @since 1.0.0
     * @param $key
     * @param array $options
     * @return mixed
     */
    public function sort($key,array $options = []);

    /**
     * @doc https://redis.io/commands/spop
     * @since 1.0.0
     * @param $key
     * @param $count
     * @return mixed
     */
    public function sPop($key,$count);

    /**
     * @doc https://redis.io/commands/srandmember
     * @since 1.0.0
     * @param $key
     * @param $count
     * @return mixed
     */
    public function sRandMember($key,$count);

    /**
     * @doc https://redis.io/commands/srem
     * @since 1.0.0
     * @param $key
     * @param ...$members
     * @return mixed
     */
    public function sRem($key,...$members);

    /**
     * @doc https://redis.io/commands/strlen
     * @since 2.2.0
     * @param $key
     * @return mixed
     */
    public function strLen($key);

    /**
     * @doc https://redis.io/commands/subscribe
     * @since 2.0.0
     * @param ...$channels
     * @return mixed
     */
    public function subscribe(...$channels);

    /**
     * @doc https://redis.io/commands/sunion
     * @since 1.0.0
     * @param ...$keys
     * @return mixed
     */
    public function sUnion(...$keys);

    /**
     * @doc https://redis.io/commands/sunionstore
     * @since 1.0.0
     * @param $dst
     * @param ...$keys
     * @return mixed
     */
    public function sUnionStore($dst,...$keys);

    /**
     * @doc https://redis.io/commands/swapdb
     * @since 4.0.0
     * @param $opt
     * @param $dst
     * @param ...$keys
     * @return mixed
     */
    public function sWapBb($opt,$dst,...$keys);

    /**
     * @doc https://redis.io/commands/sadd
     * @since 1.0.0
     * @param $key
     * @param ...$members
     * @return mixed
     */
    public function sAdd($key,...$members);

    /**
     * @doc https://redis.io/commands/save
     * @since 1.0.0
     * @return mixed
     */
    public function save();

    /**
     * @doc https://redis.io/commands/scard
     * @since 1.0.0
     * @param $key
     * @return mixed
     */
    public function sCard($key);

    /**
     * @doc https://redis.io/commands/sdiff
     * @since 1.0.0
     * @param ...$keys
     * @return mixed
     */
    public function sDiff(...$keys);

    /**
     * @doc https://redis.io/commands/sdiffstore
     * @since 1.0.0
     * @param $dst
     * @param ...$keys
     * @return mixed
     */
    public function sDiffStore($dst,...$keys);
}