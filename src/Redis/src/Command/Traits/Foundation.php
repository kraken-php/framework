<?php

namespace Kraken\Redis\Command\Traits;

use Kraken\Redis\Command\Builder;

trait Foundation
{
    private $command;
    private $args;

    public function auth($password)
    {
        $command = \Kraken\Redis\Command\AUTH;
        $args = [$password];

        return Builder::build($command, $args);
    }

    public function append($key, $value)
    {
        $command = \Kraken\Redis\Command\APPEND;
        $args = [$key, $value];

        return Builder::build($command, $args);
    }

    public function bgRewriteAoF()
    {
        $command = \Kraken\Redis\Command\BGREWRITEAOF;

        return Builder::build($command);
    }

    public function bgSave()
    {
        $command = \Kraken\Redis\Command\BGSAVE;

        return Builder::build($command);
    }

    public function bitCount($key, $start = 0, $end = 0)
    {
        $command = \Kraken\Redis\Command\BITCOUNT;
        $args = [$key, $start, $end];

        return Builder::build($command, $args);
    }

    public function bitField($command, ...$param)
    {
        $command = \Kraken\Redis\Command\BITFIELD;
    }

    public function bitOp($operation, $dstKey, ...$keys)
    {
        $command = \Kraken\Redis\Command\BITOP;
    }

    public function bitPos($key, $bit, $start = 0, $end = 0)
    {
       $command = \Kraken\Redis\Command\BITPOS;
    }

    public function blPop(array $keys, $timeout)
    {
        // TODO: Implement blPop() method.
        $command = \Kraken\Redis\Command\BLPOP;

    }

    public function brPop(array $keys, $timeout)
    {
        // TODO: Implement brPop() method.
        $command = \Kraken\Redis\Command\BRPOP;

    }

    public function brPopLPush($src, $dst, $timeout)
    {
        // TODO: Implement brPopLPush() method.
        $command = \Kraken\Redis\Command\BRPOPLPUSH;

    }

    public function decr($key)
    {
        // TODO: Implement decr() method.
        $command = \Kraken\Redis\Command\DECR;

    }

    public function decrBy($key, $decrement)
    {
        // TODO: Implement decrBy() method.
        $command = \Kraken\Redis\Command\DECRBY;

    }

    public function discard()
    {
        // TODO: Implement discard() method.
        $command = \Kraken\Redis\Command\DISCARD;

    }

    public function dump($key)
    {
        // TODO: Implement dump() method.
        $command = \Kraken\Redis\Command\DUMP;

    }

    public function exists(...$keys)
    {
        // TODO: Implement exists() method.
        $command = \Kraken\Redis\Command\EXISTS;

    }

    public function expire($key, $seconds)
    {
        // TODO: Implement expire() method.
        $command = \Kraken\Redis\Command\EXPIRE;

    }

    public function expireAt($key, $timestamp)
    {
        // TODO: Implement expireAt() method.
        $command = \Kraken\Redis\Command\EXPIREAT;

    }

    public function get($key)
    {
        $command = \Kraken\Redis\Command\GET;
        $args = [$key];

        return Builder::build($command, $args);
    }

    public function getBit($key, $offset)
    {
        // TODO: Implement getBit() method.
        $command = \Kraken\Redis\Command\GETBIT;

    }

    public function getRange($key, $start, $end)
    {
        // TODO: Implement getRange() method.
        $command = \Kraken\Redis\Command\GETRANGE;

    }

    public function getSet($key, $value)
    {
        // TODO: Implement getSet() method.
        $command = \Kraken\Redis\Command\GETSET;

    }

    public function incr($key)
    {
        $this->command = \Kraken\Redis\Command\INCR;
        $this->args = [$key];

        return $this;
    }

    public function incrBy($key, $increment)
    {
        // TODO: Implement incrBy() method.
        $command = \Kraken\Redis\Command\INCRBY;

    }

    public function incrByFloat($key, $increment)
    {
        // TODO: Implement incrByFloat() method.
        $command = \Kraken\Redis\Command\INCRBYFLOAT;

    }

    public function multi()
    {
        // TODO: Implement multi() method.
        $command = \Kraken\Redis\Command\MULTI;

    }

    public function persist($key)
    {
        // TODO: Implement persist() method.
        $command = \Kraken\Redis\Command\PERSIST;

    }

    public function pExpire($key, $milliseconds)
    {
        // TODO: Implement pExpire() method.
        $command = \Kraken\Redis\Command\PEXPIRE;

    }

    public function pExpireAt($key, $milliseconds)
    {
        // TODO: Implement pExpireAt() method.
        $command = \Kraken\Redis\Command\PEXPIREAT;

    }

    public function sync()
    {
        // TODO: Implement sync() method.
        $command = \Kraken\Redis\Command\SYNC;

    }

    public function time()
    {
        // TODO: Implement time() method.
        $command = \Kraken\Redis\Command\TIME;

    }

    public function touch(...$keys)
    {
        // TODO: Implement touch() method.
        $command = \Kraken\Redis\Command\TOUCH;

    }

    public function ttl($key)
    {
        // TODO: Implement ttl() method.
        $command = \Kraken\Redis\Command\TTL;

    }

    public function type($key)
    {
        // TODO: Implement type() method.
        $command = \Kraken\Redis\Command\TYPE;

    }

    public function unLink(...$keys)
    {
        // TODO: Implement unLink() method.
        $command = \Kraken\Redis\Command\UNLINK;

    }

    public function unWatch()
    {
        // TODO: Implement unWatch() method.
        $command = \Kraken\Redis\Command\UNWATCH;

    }

    public function wait($numSlaves, $timeout)
    {
        // TODO: Implement wait() method.
        $command = \Kraken\Redis\Command\WAIT;

    }

    public function watch(...$keys)
    {
        // TODO: Implement watch() method.
        $command = \Kraken\Redis\Command\WATCH;

    }

    public function select($index)
    {
        // TODO: Implement select() method.
        $command = \Kraken\Redis\Command\SELECT;

        return $this;
    }

    public function set($key, $value, array $options)
    {
        $command = \Kraken\Redis\Command\SET;
        array_unshift($options, $key, $value);
        $args = $options;

        return Builder::build($command, $args);
    }

    public function setBit($key, $offset, $value)
    {
        // TODO: Implement setBit() method.
        $command = \Kraken\Redis\Command\SETBIT;

    }

    public function setEx($key, $seconds, $value)
    {
        // TODO: Implement setEx() method.
        $command = \Kraken\Redis\Command\SETEX;

    }

    public function setNx($key, $value)
    {
        // TODO: Implement setNx() method.
        $command = \Kraken\Redis\Command\SETNX;

    }

    public function randomKey()
    {
        // TODO: Implement randomKey() method.
        $command = \Kraken\Redis\Command\RANDOMKEY;

    }

    public function readOnly()
    {
        // TODO: Implement readOnly() method.
        $command = \Kraken\Redis\Command\READONLY;

    }

    public function readWrtie()
    {
        // TODO: Implement readWrtie() method.
        $command = \Kraken\Redis\Command\READWRITE;

    }

    public function rename($key, $newKey)
    {
        // TODO: Implement rename() method.
        $command = \Kraken\Redis\Command\RENAME;

    }

    public function renameNx($key, $newKey)
    {
        // TODO: Implement renameNx() method.
        $command = \Kraken\Redis\Command\RENAMENX;

    }

    public function restore($key, $ttl, $value)
    {
        // TODO: Implement restore() method.
        $command = \Kraken\Redis\Command\RESTORE;

    }

    public function ping($message = 'PING')
    {
        // TODO: Implement ping() method.
        $command = \Kraken\Redis\Command\PING;

    }

    public function quit()
    {
        // TODO: Implement quit() method.
        $command = \Kraken\Redis\Command\QUIT;

    }

    public function setRange($key, $offset, $value)
    {
        //
        $command = \Kraken\Redis\Command\SETRANGE;

    }

    public function pTtl($key)
    {
        $command = \Kraken\Redis\Command\PTTL;

    }

    public function pSetEx($key, $milliseconds, $value)
    {
        $command = \Kraken\Redis\Command\PSETEX;

    }

}