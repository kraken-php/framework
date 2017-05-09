<?php

namespace Kraken\Redis;

use Kraken\Event\EventEmitterInterface;
use Kraken\Promise\PromiseInterface;

/**
 * Simple interface for executing redis commands
 *
 * @event data(ModelInterface $messageModel)
 * @event error(Exception $error)
 * @event close()
 *
 * @event message($channel, $message)
 * @event subscribe($channel, $numberOfChannels)
 * @event unsubscribe($channel, $numberOfChannels)
 *
 * @event pmessage($pattern, $channel, $message)
 * @event psubscribe($channel, $numberOfChannels)
 * @event punsubscribe($channel, $numberOfChannels)
 *
 * @event monitor(ModelInterface $statusModel)
 *
 */

interface ClientInterface extends EventEmitterInterface
{
    public function append($key,$value);
    public function auth($password);
    public function bgrewriteaof();
    public function bgsave();
    public function bitcount($key,$start=0,$end=0);
    //todo : definition
    public function bitfield();
    public function bitop($operation,$dstKey,...$keys);
    public function bitpos($key,$bit,$start=0,$end=0);
    public function decr($key);
    public function decrby($key,$decrement);
    public function get($key);
    public function getbit($key,$offset);
    public function getrange($key,$start,$end);
    public function getset($key,$value);
    public function incr($key);
    public function incrby($key,$increment);
    public function incrbyfloat($key,$increment);
    public function mget($key,...$values);
    public function mset(array $kvMap);
    public function msetnx();
    public function psetex();
    public function set();
    public function setbit();



    /**
     * Checks if the client is busy, i.e. still has any requests pending
     *
     * @return boolean
     */
    public function isBusy();

    /**
     * end connection once all pending requests have been replied to
     *
     * @uses self::close() once all replies have been received
     * @see self::close() for closing the connection immediately
     */
    public function end();

    /**
     * close connection immediately
     *
     * This will emit the "close" event.
     *
     * @see self::end() for closing the connection once the client is idle
     */
    public function close();
}
