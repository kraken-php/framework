<?php

namespace Kraken\Redis\Command\Traits;

use Kraken\Redis\Command\FoundationInterface;

trait Foundation
{
    public function auth($password)
    {
        // TODO: Implement auth() method.
    }

    public function append($key, $value)
    {
        // TODO: Implement append() method.
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
        // TODO: Implement get() method.
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
        // TODO: Implement incr() method.
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

    public function set($key, $value, array $options)
    {
        // TODO: Implement set() method.
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