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
     * @param resource $stream
     * @param callable $listener
     */
    public function addReadStream($stream, callable $listener);

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param resource $stream
     * @param callable $listener
     */
    public function addWriteStream($stream, callable $listener);

    /**
     * Remove the read event listener for the given stream.
     *
     * @param resource $stream
     */
    public function removeReadStream($stream);

    /**
     * Remove the write event listener for the given stream.
     *
     * @param resource $stream
     */
    public function removeWriteStream($stream);

    /**
     * Remove all listeners for the given stream.
     *
     * @param resource $stream
     */
    public function removeStream($stream);

    /**
     * @param float $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback);

    /**
     * @param float $interval
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
