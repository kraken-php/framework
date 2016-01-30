<?php

namespace Kraken\Loop\Timer;

interface TimerCollectionInterface
{
    /**
     * @param string $name
     * @param TimerInterface $timer
     */
    public function add($name, TimerInterface $timer);

    /**
     * @param string $name
     * @return TimerInterface
     */
    public function get($name);

    /**
     * @param string $name
     */
    public function remove($name);
}
