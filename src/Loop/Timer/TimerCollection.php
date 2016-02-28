<?php

namespace Kraken\Loop\Timer;

class TimerCollection implements TimerCollectionInterface
{
    /**
     * @var TimerInterface[]
     */
    protected $timers;

    /**
     *
     */
    public function __construct()
    {
        $this->timers = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->timers);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function existsTimer($name)
    {
        return isset($this->timers[$name]);
    }

    /**
     * @param string $name
     * @param TimerInterface $timer
     */
    public function addTimer($name, TimerInterface $timer)
    {
        $this->timers[$name] = $timer;
    }

    /**
     * @param string $name
     * @return TimerInterface|null
     */
    public function getTimer($name)
    {
        return $this->existsTimer($name) ? $this->timers[$name] : null;
    }

    /**
     * @param string $name
     */
    public function removeTimer($name)
    {
        unset($this->timers[$name]);
    }
}
