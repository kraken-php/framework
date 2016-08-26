<?php

namespace Kraken\Loop;

use Kraken\Loop\Timer\TimerInterface;

interface LoopInterface
{
    /**
     * Check if loop is currently running.
     *
     * @return bool
     */
    public function isRunning();

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
     * Enqueue a callback to be invoked once after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is not guaranteed.
     *
     * @param float $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback);

    /**
     * Enqueue a callback to be invoked repeatedly after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is not guaranteed.
     *
     * @param float $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback);

    /**
     * Cancel a pending timer.
     *
     * @param TimerInterface $timer
     */
    public function cancelTimer(TimerInterface $timer);

    /**
     * Check if a given timer is active.
     *
     * @param TimerInterface $timer
     * @return bool
     */
    public function isTimerActive(TimerInterface $timer);

    /**
     * Schedule a callback to be invoked on the start tick of event loop.
     *
     * Callbacks are guarenteed to be executed in the order they are enqueued, before anything else.
     *
     * @param callable $listener
     */
    public function onStart(callable $listener);

    /**
     * Schedule a callback to be invoked on the stop tick of event loop.
     *
     * Callbacks are guarenteed to be executed in the order they are enqueued.
     *
     * @param callable $listener
     */
    public function onStop(callable $listener);

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued. This method is an alias for onAfterTick()
     * method.
     *
     * @param callable $listener
     */
    public function onTick(callable $listener);

    /**
     * Schedule a callback to be invoked on the next tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued, before any timer or stream events.
     *
     * @param callable $listener
     */
    public function onBeforeTick(callable $listener);

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener
     */
    public function onAfterTick(callable $listener);
}
