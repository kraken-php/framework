<?php

namespace Kraken\Redis\Command;

interface FoundationInterface
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
    public function pTtl($key);
    public function pSetEx($key,$milliseconds,$value);

}