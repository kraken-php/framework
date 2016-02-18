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
     */
    public function getActualTimer()
    {
        return $this->timer;
    }

    /**
     * @override
     */
    public function getLoop()
    {
        return new ReactLoop($this->timer->getLoop());
    }

    /**
     * @override
     */
    public function getInterval()
    {
        return $this->timer->getInterval();
    }

    /**
     * @override
     */
    public function getCallback()
    {
        return $this->timer->getCallback();
    }

    /**
     * @override
     */
    public function setData($data)
    {
        return $this->timer->setData($data);
    }

    /**
     * @override
     */
    public function getData()
    {
        return $this->timer->getData();
    }

    /**
     * @override
     */
    public function isPeriodic()
    {
        return $this->timer->isPeriodic();
    }

    /**
     * @override
     */
    public function isActive()
    {
        return $this->timer->isActive();
    }

    /**
     * @override
     */
    public function cancel()
    {
        $this->timer->cancel();
    }
}
