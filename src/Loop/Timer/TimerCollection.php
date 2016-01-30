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
     * @param TimerInterface $timer
     */
    public function add($name, TimerInterface $timer)
    {
        $this->timers[$name] = $timer;
    }

    /**
     * @param string $name
     * @return TimerInterface
     */
    public function get($name)
    {
        return $this->timers[$name];
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->timers[$name]);
    }
}
