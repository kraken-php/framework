<?php

namespace Kraken\Loop\Timer;

use Kraken\Loop\LoopModelInterface;

interface TimerInterface
{
    /**
     * Return loop.
     *
     * @return LoopModelInterface
     */
    public function getLoop();

    /**
     * Return interval of timer.
     *
     * @return float
     */
    public function getInterval();

    /**
     * Return callback attached to timer.
     *
     * @return callable
     */
    public function getCallback();

    /**
     * Return data associated with timer.
     *
     * @return mixed|null
     */
    public function getData();

    /**
     * Set data associated with timer.
     *
     * @param mixed $data
     */
    public function setData($data);

    /**
     * Check if timer is periodic.
     *
     * @return bool
     */
    public function isPeriodic();

    /**
     * Check if timer is active.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Cancel timer and unregister it from loop.
     */
    public function cancel();
}
