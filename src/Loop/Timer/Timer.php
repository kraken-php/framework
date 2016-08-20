<?php

namespace Kraken\Loop\Timer;

use Kraken\Loop\LoopModelInterface;

class Timer implements TimerInterface
{
    /**
     * @var float
     */
    const MIN_INTERVAL = 1e-6;

    /**
     * @var LoopModelInterface
     */
    protected $loop;

    /**
     * @var float
     */
    protected $interval;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $periodic;

    /**
     * @var mixed|null
     */
    protected $data;

    /**
     * @param LoopModelInterface $loop
     * @param $interval
     * @param callable $callback
     * @param bool $periodic
     * @param mixed|null $data
     */
    public function __construct(LoopModelInterface $loop, $interval, callable $callback, $periodic = false, $data = null)
    {
        if ($interval < self::MIN_INTERVAL)
        {
            $interval = self::MIN_INTERVAL;
        }

        $this->loop = $loop;
        $this->interval = (float) $interval;
        $this->callback = $callback;
        $this->periodic = (bool) $periodic;
        $this->data = $data;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->loop);
        unset($this->interval);
        unset($this->callback);
        unset($this->periodic);
        unset($this->data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPeriodic()
    {
        return $this->periodic;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isActive()
    {
        return $this->loop->isTimerActive($this);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel()
    {
        if (isset($this->loop))
        {
            $this->loop->cancelTimer($this);
        }
    }
}
