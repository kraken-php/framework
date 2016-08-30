<?php

namespace Kraken\Loop;

use Kraken\Loop\Flow\FlowController;
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
//        unset($this->loop);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getModel()
    {
        return $this->loop;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function erase($all = false)
    {
        $this->loop->erase($all);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function export(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->export($loop->getModel(), $all);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function import(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->import($loop->getModel(), $all);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function swap(LoopExtendedInterface $loop, $all = false)
    {
        $this->loop->swap($loop->getModel(), $all);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRunning()
    {
        return $this->loop->isRunning();
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
        return $this->loop->addTimer($interval, $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return $this->loop->addPeriodicTimer($interval, $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStart(callable $listener)
    {
        $this->loop->onStart($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStop(callable $listener)
    {
        $this->loop->onStop($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onTick(callable $listener)
    {
        $this->loop->onAfterTick($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeTick(callable $listener)
    {
        $this->loop->onBeforeTick($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterTick(callable $listener)
    {
        $this->loop->onAfterTick($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function tick()
    {
        $this->loop->tick();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        $this->loop->start();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->loop->stop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setFlowController($flowController)
    {
        $this->loop->setFlowController($flowController);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFlowController()
    {
        return $this->loop->getFlowController();
    }
}
