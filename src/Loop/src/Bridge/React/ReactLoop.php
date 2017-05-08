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
     * @inheritDoc
     */
    public function getActualLoop()
    {
        return $this->loop;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->loop->addReadStream($stream, $listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addWriteStream($stream, callable $listener)
    {
        $this->loop->addWriteStream($stream, $listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeReadStream($stream)
    {
        $this->loop->removeReadStream($stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeWriteStream($stream)
    {
        $this->loop->removeWriteStream($stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeStream($stream)
    {
        $this->loop->removeStream($stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addTimer($interval, callable $callback)
    {
        return new ReactTimer($this->loop->addTimer($interval, $callback));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return new ReactTimer($this->loop->addPeriodicTimer($interval, $callback));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancelTimer(\React\EventLoop\Timer\TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer->getActualTimer());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isTimerActive(\React\EventLoop\Timer\TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer->getActualTimer());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function nextTick(callable $listener)
    {
        $this->loop->onBeforeTick($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function futureTick(callable $listener)
    {
        $this->loop->onAfterTick($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function tick()
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function run()
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {}
}
