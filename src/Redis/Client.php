<?php

namespace Kraken\Redis;

use Kraken\Event\EventEmitterInterface;
use Kraken\Promise\PromiseInterface;
use Clue\Redis\Protocol\Model\ModelInterface;

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
 */
interface Client extends EventEmitterInterface
{
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
