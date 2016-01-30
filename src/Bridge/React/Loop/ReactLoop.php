<?php

namespace Kraken\Bridge\React\Loop;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class ReactLoop implements ReactLoopInterface
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param ReactLoopAwareInterface $loop
     */
    public function __construct(ReactLoopAwareInterface $loop)
    {
        $this->loop = $loop->getReactLoop();
    }

    /**
     * Returns instance of original React loop
     *
     * @return LoopInterface
     */
    public function getReactLoop()
    {
        return $this->loop;
    }

    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param stream $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->loop->addReadStream($stream, $listener);
    }

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param stream $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addWriteStream($stream, callable $listener)
    {
        $this->loop->addWriteStream($stream, $listener);
    }

    /**
     * Remove the read event listener for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeReadStream($stream)
    {
        $this->loop->removeReadStream($stream);
    }

    /**
     * Remove the write event listener for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeWriteStream($stream)
    {
        $this->loop->removeWriteStream($stream);
    }

    /**
     * Remove all listeners for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function removeStream($stream)
    {
        $this->loop->removeStream($stream);
    }

    /**
     * Enqueue a callback to be invoked once after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param numeric $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback)
    {
        return $this->loop->addTimer($interval, $callback);
    }

    /**
     * Enqueue a callback to be invoked repeatedly after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param numeric $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return $this->loop->addPeriodicTimer($interval, $callback);
    }

    /**
     * Cancel a pending timer.
     *
     * @param TimerInterface $timer The timer to cancel.
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer);
    }

    /**
     * Check if a given timer is active.
     *
     * @param TimerInterface $timer The timer to check.
     *
     * @return boolean True if the timer is still enqueued for execution.
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer);
    }

    /**
     * Schedule a callback to be invoked on the next tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued,
     * before any timer or stream events.
     *
     * @param callable $listener The callback to invoke.
     */
    public function nextTick(callable $listener)
    {
        $this->loop->nextTick($listener);
    }

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function futureTick(callable $listener)
    {
        $this->loop->futureTick($listener);
    }

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick()
    {
        $this->loop->tick();
    }

    /**
     * Run the event loop until there are no more tasks to perform.
     */
    public function run()
    {
        $this->loop->run();
    }

    /**
     * Instruct a running event loop to stop.
     */
    public function stop()
    {
        $this->loop->stop();
    }
}
