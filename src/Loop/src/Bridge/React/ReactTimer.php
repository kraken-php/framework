<?php

namespace Kraken\Loop\Bridge\React;

use Kraken\Loop\Timer\TimerInterface;

class ReactTimer implements ReactTimerInterface
{
    /**
     * @var TimerInterface
     */
    protected $timer;

    /**
     * @param TimerInterface $timer
     */
    public function __construct(TimerInterface $timer)
    {
        $this->timer = $timer;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->timer);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getActualTimer()
    {
        return $this->timer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return new ReactLoop($this->timer->getLoop());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getInterval()
    {
        return $this->timer->getInterval();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getCallback()
    {
        return $this->timer->getCallback();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setData($data)
    {
        return $this->timer->setData($data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getData()
    {
        return $this->timer->getData();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPeriodic()
    {
        return $this->timer->isPeriodic();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isActive()
    {
        return $this->timer->isActive();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel()
    {
        $this->timer->cancel();
    }
}
