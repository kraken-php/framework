<?php

namespace Kraken\Loop;

use Kraken\Loop\Timer\TimerInterface;

class Loop implements LoopExtendedInterface
{
    /**
     * @var LoopModelInterface
     */
    protected $loop;

    /**
     * @param LoopModelInterface
     */
    public function __construct(LoopModelInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->loop);
    }

    /**
     * @return LoopModelInterface
     */
    public function model()
    {
        return $this->loop;
    }

    /**
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function flush($all = false)
    {
        $this->loop->flush($all);

        return $this;
    }

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function export(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->export($loop->model(), $all);

        return $this;
    }

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function import(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->import($loop->model(), $all);

        return $this;
    }

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function swap(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->swap($loop->model(), $all);

        return $this;
    }

    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param stream   $stream   The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->loop->addReadStream($stream, $listener);
    }

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param stream   $stream   The PHP stream resource to check.
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
     * @param numeric $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback)
    {
        return $this->loop->addTimer($interval, $callback);
    }

    /**
     * @param numeric $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return $this->loop->addPeriodicTimer($interval, $callback);
    }

    /**
     * @param TimerInterface $timer
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer);
    }

    /**
     * @param TimerInterface $timer
     * @return bool
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer);
    }

    /**
     * @param callable $listener
     */
    public function startTick(callable $listener)
    {
        $this->loop->startTick($listener);
    }

    /**
     * @param callable $listener
     */
    public function stopTick(callable $listener)
    {
        $this->loop->stopTick($listener);
    }

    /**
     * @param callable $listener
     */
    public function beforeTick(callable $listener)
    {
        $this->loop->beforeTick($listener);
    }

    /**
     * @param callable $listener
     */
    public function afterTick(callable $listener)
    {
        $this->loop->afterTick($listener);
    }

    /**
     *
     */
    public function tick()
    {
        $this->loop->tick();
    }

    /**
     *
     */
    public function start()
    {
        $this->loop->start();
    }

    /**
     *
     */
    public function stop()
    {
        $this->loop->stop();
    }
}
