<?php

namespace Kraken\Loop;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Timer\TimerInterface;

interface LoopModelInterface
{
    /**
     * @return bool
     */
    public function isRunning();

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
     * Enqueue a callback to be invoked once after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param numeric  $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback);

    /**
     * Enqueue a callback to be invoked repeatedly after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param numeric  $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback);

    /**
     * Cancel a pending timer.
     *
     * @param TimerInterface $timer The timer to cancel.
     */
    public function cancelTimer(TimerInterface $timer);

    /**
     * Check if a given timer is active.
     *
     * @param TimerInterface $timer The timer to check.
     *
     * @return boolean True if the timer is still enqueued for execution.
     */
    public function isTimerActive(TimerInterface $timer);

    /**
     * Schedule a callback to be invoked on the start tick of event loop.
     *
     * Callbacks are guarenteed to be executed in the order they are enqueued, before anything else.
     *
     * @param callable $listener
     */
    public function startTick(callable $listener);

    /**
     * Schedule a callback to be invoked on the stop tick of event loop.
     *
     * Callbacks are guarenteed to be executed in the order they are enqueued.
     *
     * @param callable $listener
     */
    public function stopTick(callable $listener);

    /**
     * Schedule a callback to be invoked on the next tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued, before any timer or stream events.
     *
     * @param callable $listener The callback to invoke.
     */
    public function beforeTick(callable $listener);

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function afterTick(callable $listener);

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
     * @param mixed $flowController
     */
    public function setFlowController($flowController);

    /**
     * @return FlowController
     */
    public function getFlowController();

    /**
     * @param bool $all
     * @return LoopModelInterface
     */
    public function flush($all = false);

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function export(LoopModelInterface $loop, $all = false);

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function import(LoopModelInterface $loop, $all = false);

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function swap(LoopModelInterface $loop, $all = false);
}
