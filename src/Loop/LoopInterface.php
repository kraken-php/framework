<?php

namespace Kraken\Loop;

use Kraken\Loop\Timer\TimerInterface;

/**
 * @event start
 * @event stop
 */
interface LoopInterface
{
    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param stream   $stream   The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, callable $listener);

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param stream   $stream   The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addWriteStream($stream, callable $listener);

    /**
     * Remove the read event listener for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeReadStream($stream);

    /**
     * Remove the write event listener for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeWriteStream($stream);

    /**
     * Remove all listeners for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeStream($stream);

    /**
     * @param numeric $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback);

    /**
     * @param numeric $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback);

    /**
     * @param TimerInterface $timer
     */
    public function cancelTimer(TimerInterface $timer);

    /**
     * @param TimerInterface $timer
     * @return bool
     */
    public function isTimerActive(TimerInterface $timer);

    /**
     * @param callable $listener
     */
    public function startTick(callable $listener);

    /**
     * @param callable $listener
     */
    public function beforeTick(callable $listener);

    /**
     * @param callable $listener
     */
    public function afterTick(callable $listener);
}
