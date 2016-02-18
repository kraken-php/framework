<?php

namespace Kraken\Loop\Bridge\React;

use Kraken\Loop\LoopInterface;

class ReactLoop implements ReactLoopInterface
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
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
     * @override
     */
    public function getActualLoop()
    {
        return $this->loop;
    }

    /**
     * @override
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->loop->addReadStream($stream, $listener);
    }

    /**
     * @override
     */
    public function addWriteStream($stream, callable $listener)
    {
        $this->loop->addWriteStream($stream, $listener);
    }

    /**
     * @override
     */
    public function removeReadStream($stream)
    {
        $this->loop->removeReadStream($stream);
    }

    /**
     * @override
     */
    public function removeWriteStream($stream)
    {
        $this->loop->removeWriteStream($stream);
    }

    /**
     * @override
     */
    public function removeStream($stream)
    {
        $this->loop->removeStream($stream);
    }

    /**
     * @override
     */
    public function addTimer($interval, callable $callback)
    {
        return new ReactTimer($this->loop->addTimer($interval, $callback));
    }

    /**
     * @override
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return new ReactTimer($this->loop->addPeriodicTimer($interval, $callback));
    }

    /**
     * @override
     */
    public function cancelTimer(\React\EventLoop\Timer\TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer->getActualTimer());
    }

    /**
     * @override
     */
    public function isTimerActive(\React\EventLoop\Timer\TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer->getActualTimer());
    }

    /**
     * @override
     */
    public function nextTick(callable $listener)
    {
        $this->loop->beforeTick($listener);
    }

    /**
     * @override
     */
    public function futureTick(callable $listener)
    {
        $this->loop->afterTick($listener);
    }

    /**
     * @override
     */
    public function tick()
    {}

    /**
     * @override
     */
    public function run()
    {}

    /**
     * @override
     */
    public function stop()
    {}
}
