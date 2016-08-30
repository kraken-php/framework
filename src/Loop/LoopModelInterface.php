<?php

namespace Kraken\Loop;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Timer\TimerInterface;

interface LoopModelInterface
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

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick();

    /**
     * Run the event loop until there are no more tasks to perform.
     */
    public function start();

    /**
     * Instruct a running event loop to stop.
     */
    public function stop();

    /**
     * Set FlowController used by model.
     *
     * @param mixed $flowController
     */
    public function setFlowController($flowController);

    /**
     * Return FlowController used by model.
     *
     * @return FlowController
     */
    public function getFlowController();

    /**
     * Flush loop.
     *
     * @param bool $all
     * @return LoopModelInterface
     */
    public function erase($all = false);

    /**
     * Export loop not fired handlers and/or streams to another loop model.
     *
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function export(LoopModelInterface $loop, $all = false);

    /**
     * Import handlers and/or streams from another loop model.
     *
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function import(LoopModelInterface $loop, $all = false);

    /**
     * Swap handlers and/or stream between loop models.
     *
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function swap(LoopModelInterface $loop, $all = false);
}
