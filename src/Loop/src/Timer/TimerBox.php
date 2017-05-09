<?php

namespace Kraken\Loop\Timer;

use SplObjectStorage;
use SplPriorityQueue;

class TimerBox
{
    /**
     * @var float
     */
    protected $time;

    /**
     * @var SplObjectStorage
     */
    protected $timers;

    /**
     * @var SplPriorityQueue
     */
    protected $scheduler;

    /**
     *
     */
    public function __construct()
    {
        $this->timers = new SplObjectStorage();
        $this->scheduler = new SplPriorityQueue();
    }

    /**
     * @return float
     */
    public function updateTime()
    {
        return $this->time = microtime(true);
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time ?: $this->updateTime();
    }

    /**
     * @param TimerInterface $timer
     * @return bool
     */
    public function contains(TimerInterface $timer)
    {
        return $this->timers->contains($timer);
    }

    /**
     * @param TimerInterface $timer
     */
    public function add(TimerInterface $timer)
    {
        $interval = $timer->getInterval();
        $scheduledAt = $interval + $this->getTime();

        $this->timers->attach($timer, $scheduledAt);
        $this->scheduler->insert($timer, -$scheduledAt);
    }

    /**
     * @param TimerInterface $timer
     */
    public function remove(TimerInterface $timer)
    {
        $this->timers->detach($timer);
    }

    /**
     * @return TimerInterface|null
     */
    public function getFirst()
    {
        while ($this->scheduler->count())
        {
            $timer = $this->scheduler->top();

            if ($this->timers->contains($timer))
            {
                return $this->timers[$timer];
            }

            $this->scheduler->extract();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->timers) === 0;
    }

    /**
     *
     */
    public function tick()
    {
        $time = $this->updateTime();
        $timers = $this->timers;
        $scheduler = $this->scheduler;

        while (!$scheduler->isEmpty())
        {
            $timer = $scheduler->top();

            if (!isset($timers[$timer]))
            {
                $scheduler->extract();
                $timers->detach($timer);
                continue;
            }

            if ($timers[$timer] >= $time)
            {
                break;
            }

            $scheduler->extract();

            $callback = $timer->getCallback();
            $callback($timer);

            if ($timer->isPeriodic() && isset($timers[$timer]))
            {
                $timers[$timer] = $scheduledAt = $timer->getInterval() + $time;
                $scheduler->insert($timer, -$scheduledAt);
            }
            else
            {
                $timers->detach($timer);
            }
        }
    }
}
