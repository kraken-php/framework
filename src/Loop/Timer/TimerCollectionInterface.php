<?php

namespace Kraken\Loop\Timer;

interface TimerCollectionInterface
{
    /**
     * @return TimerInterface[]
     */
    public function getTimers();

    /**
     * @param string $name
     * @return bool
     */
    public function existsTimer($name);

    /**
     * @param string $name
     * @param TimerInterface $timer
     */
    public function addTimer($name, TimerInterface $timer);

    /**
     * @param string $name
     * @return TimerInterface|null
     */
    public function getTimer($name);

    /**
     * @param string $name
     */
    public function removeTimer($name);
}
