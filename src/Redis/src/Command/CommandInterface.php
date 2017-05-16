<?php

namespace Kraken\Redis\Command;

interface CommandInterface
{
    public function auth($password);
    public function append($key,$value);
    public function bgRewriteAoF();
    public function bgSave();
    public function bitCount($key,$start=0,$end=0);
    public function bitField($command,...$param);
    public function bitOp($operation,$dstKey,...$keys);
    public function bitPos($key,$bit,$start=0,$end=0);
    public function blPop(array $keys,$timeout);
    public function brPop(array $keys,$timeout);
    public function brPopLPush($src,$dst,$timeout);
    public function decr($key);
    public function decrBy($key,$decrement);
    public function discard();
    public function dump($key);
    public function exists(...$keys);
    public function expire($key,$seconds);
    public function expireAt($key,$timestamp);
    public function get($key);
    public function getBit($key,$offset);
    public function getRange($key,$start,$end);
    public function getSet($key,$value);
    public function hDel($key,...$fields);
    public function hExsits($key,$field);
    public function hGet($key,$field);
    public function hGetAll($key);
    public function hIncrBy($key,$field,$incrment);
    public function hIncrByFloat($key,$field,$increment);
    public function hKeys($key);
    public function hLen($key);
    public function hMGet($key,...$fields);
    public function hMSet($key,array $fvMap);
    public function hSet($key,$field,$value);
    public function hSetNx($key,$filed,$value);
    public function hStrLen($key,$field);
    public function hVals($key);
    public function incr($key);
    public function incrBy($key,$increment);
    public function incrByFloat($key,$increment);
    public function multi();
    public function persist($key);
    public function pExpire($key,$milliseconds);
    public function pExpireAt($key,$milliseconds);
    public function sync();
    public function time();
    public function touch(...$keys);
    public function ttl($key);
    public function type($key);
    public function unLink(...$keys);
    public function unWatch();
    public function wait($numSlaves,$timeout);
    public function watch(...$keys);
    public function select($index);
    public function set($key,$value,array $options);
    public function setBit($key,$offset,$value);
    public function setEx($key,$seconds,$value);
    public function setNx($key,$value);
    public function randomKey();
    public function readOnly();
    public function readWrtie();
    public function rename($key,$newKey);
    public function renameNx($key,$newKey);
    public function restore($key,$ttl,$value);
    public function ping($message='pong');
    public function quit();
    public function setRange($key,$offset,$value);
    public function geoAdd($key,array $coordinates);
    public function geoHash($key,...$members);
    public function geoPos($key,...$members);
    public function geoDist($key,$memberA,$memberB,$unit);
    public function geoRadius($key,$longitude,$latitude,$unit,$command,$count,$sort);
    public function geoRadiusByMember($key,$member,$unit,$command,$count,$sort,$store,$storeDist);
    public function pTtl($key);
    public function pSetEx($key,$milliseconds,$value);
    public function pSubscribe(...$patterns);
    public function pubSub($command,array $args = []);
    public function publish($channel,$message);
    public function pUnsubscribe(...$patterns);
    public function unSubscribe(...$channels);
    public function lIndex($key,$index);
    public function lInsert($key,$action,$pivot,$value);
    public function lLen($key);
    public function lPop($key);
    public function lPush(array $kvMap);
    public function lPushX($key,$value);
    public function lRange($key,$start,$stop);
    public function lRem($key,$count,$value);
    public function lSet($key,$index,$value);
    public function lTrim($key,$start,$stop);
    public function mGet($key,...$values);
    public function mSet(array $kvMap);
    public function monitor();
    public function move($key,$db);
    public function mSetNx($kvMap);
    public function rPop($key);
    public function rPopLPush($src,$dst);
    public function rPush($key,...$values);
    public function rPushX($key,$value);
    public function pFAdd($key,...$elements);
    public function pFCount(...$keys);
    public function pFMerge(array $dsKeyMap);
    public function clientList();
    public function clientGetName();
    public function clientPause();
    public function clientReply($operation);
    public function clientSetName($connetionName);
    public function clusterAddSlots(...$slots);
    public function clusterCountFailureReports($nodeId);
    public function clusterCountKeysInSlot($slot);
    public function clusterDelSlots(...$slots);
    public function clusterFailOver($operation);
    public function clusterForget($nodeId);
    public function clusterGetKeyInSlot($slot,$count);
    public function clusterInfo();
    public function clusterKeySlot($key);
    public function clusterMeet($ip,$port);
    public function clusterNodes();
    public function clusterReplicate($nodeId);
    public function clusterReset($mode);
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
     * @return mixed
     */
    public function clusterSlots();
    public function flushAll($isAsync);
    public function flushDb($isAsync);
    public function zAdd($key,array $options = []);
    public function zCard($key);
    public function zCount($key,$min,$max);
    public function zIncrBy($key,$increment,$member);
    public function zInterStore($dst,$numKeys);
    public function zLexCount($key,$min,$max);
    public function zRange($key,$star,$stop,array $options = []);
    public function zRangeByLex($key,$min,$max,array $options = []);
    public function zRevRangeByLex($key,$max,$min,array $options = []);
    public function zRangeByScore($key,$min,$max,array $options = []);
    public function zRank($key,$member);
    public function zRem($key,...$members);
    public function zRemRangeByLex($key,$min,$max);
    public function zRemRangeByRank($key,$start,$stop);
    public function zRemRangeByScore($key,$min,$max);
    public function zRevRange($key,$start,$stop,array $options = []);
    public function zRevRangeByScore($key,$max,$min,array $options = []);
    public function zRevRank($key,$member);
    public function zScore($key,$member);
    public function zUniionScore($dst,$numKeys);
    public function scan($cursor,array $options = []);
    public function sScan($key,$cursor,array $options = []);
    public function hScan($key,$cursor,array $options = []);
    public function zScan($key,$cursor,array $options = []);
    public function sInter(...$keys);
    public function sInterStore($dst,...$keys);
    public function sIsMember($key,$member);
    public function slaveOf($host,$port);
    public function sLowLog($command,array $args=[]);
    public function sMembers($key);
    public function sMove($src,$dst,$members);
    public function sort($key,array $options = []);
    public function sPop($key,$count);
    public function sRandMember($key,$count);
    public function sRem($key,...$members);
    public function strLen($key);
    public function subscribe(...$channels);
    public function sUnion(...$keys);
    public function sUnionStore($dst,...$keys);
    public function sWapBb($opt,$dst,...$keys);
    public function sAdd($key,...$members);
    public function save();
    public function sCard($key);
    public function sDiff(...$keys);
    public function sDiffStore($dst,...$keys);
}