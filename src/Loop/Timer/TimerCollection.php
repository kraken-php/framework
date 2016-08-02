<?php

namespace Kraken\Loop\Timer;

class TimerCollection implements TimerCollectionInterface
{
    /**
     * @var TimerInterface[]
     */
    protected $timers;

    /**
     * @param TimerInterface[] $timers
     */
    public function __construct($timers = [])
    {
        $this->timers = [];

        foreach ($timers as $name=>$timer)
        {
            $this->addTimer($name, $timer);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->timers);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimers()
    {
        return $this->timers;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsTimer($name)
    {
        return isset($this->timers[$name]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addTimer($name, TimerInterface $timer)
    {
        $this->timers[$name] = $timer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimer($name)
    {
        return $this->existsTimer($name) ? $this->timers[$name] : null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeTimer($name)
    {
        unset($this->timers[$name]);
    }
}
