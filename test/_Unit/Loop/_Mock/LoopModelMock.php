<?php

namespace Kraken\_Unit\Loop\_Mock;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\LoopModelInterface;
use Kraken\Loop\Timer\TimerInterface;

class LoopModelMock implements LoopModelInterface
{
    /**
     * @var mixed[][]
     */
    private $calls = [];

    /**
     * @param string $name
     * @param mixed[] $args
     */
    public function __call($name, $args = [])
    {
        $this->applyCall($name, $args);
    }

    /**
     * @return mixed[][]|null
     */
    public function getCall($name)
    {
        return isset($this->calls[$name]) ? $this->calls[$name] : null;
    }

    /**
     * @return mixed[]
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @param string $name
     * @param mixed[] $args
     */
    private function applyCall($name, $args = [])
    {
        if (!isset($this->calls[$name]))
        {
            $this->calls[$name] = [];
        }

        $this->calls[$name][] = $args;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        $this->applyCall(__METHOD__, []);
    }

    /**
     * @param resource $stream
     * @param callable $listener
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->applyCall(__METHOD__, [ $stream, $listener ]);
    }

    /**
     * @param resource $stream
     * @param callable $listener
     */
    public function addWriteStream($stream, callable $listener)
    {
        $this->applyCall(__METHOD__, [ $stream, $listener ]);
    }

    /**
     * @param resource $stream
     */
    public function removeReadStream($stream)
    {
        $this->applyCall(__METHOD__, [ $stream ]);
    }

    /**
     * @param resource $stream
     */
    public function removeWriteStream($stream)
    {
        $this->applyCall(__METHOD__, [ $stream ]);
    }

    /**
     * @param resource $stream
     */
    public function removeStream($stream)
    {
        $this->applyCall(__METHOD__, [ $stream ]);
    }

    /**
     * @param int $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback)
    {
        $this->applyCall(__METHOD__, [ $interval, $callback ]);
    }

    /**
     * @param int $interval
     * @param callable $callback
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        $this->applyCall(__METHOD__, [ $interval, $callback ]);
    }

    /**
     * @param TimerInterface $timer
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->applyCall(__METHOD__, [ $timer ]);
    }

    /**
     * @param TimerInterface $timer
     * @return boolean
     */
    public function isTimerActive(TimerInterface $timer)
    {
        $this->applyCall(__METHOD__, [ $timer ]);
    }

    /**
     * @param callable $listener
     */
    public function startTick(callable $listener)
    {
        $this->applyCall(__METHOD__, [ $listener ]);
    }

    /**
     * @param callable $listener
     */
    public function stopTick(callable $listener)
    {
        $this->applyCall(__METHOD__, [ $listener ]);
    }

    /**
     * @param callable $listener
     */
    public function beforeTick(callable $listener)
    {
        $this->applyCall(__METHOD__, [ $listener ]);
    }

    /**
     * @param callable $listener
     */
    public function afterTick(callable $listener)
    {
        $this->applyCall(__METHOD__, [ $listener ]);
    }

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick()
    {
        $this->applyCall(__METHOD__, []);
    }

    /**
     * Run the event loop until there are no more tasks to perform.
     */
    public function start()
    {
        $this->applyCall(__METHOD__, []);
    }

    /**
     * Instruct a running event loop to stop.
     */
    public function stop()
    {
        $this->applyCall(__METHOD__, []);
    }

    /**
     * @param mixed $flowController
     */
    public function setFlowController($flowController)
    {
        $this->applyCall(__METHOD__, [ $flowController ]);
    }

    /**
     * @return FlowController
     */
    public function getFlowController()
    {
        $this->applyCall(__METHOD__, []);
    }

    /**
     * @param bool $all
     * @return LoopModelInterface
     */
    public function flush($all = false)
    {
        $this->applyCall(__METHOD__, [ $all ]);
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function export(LoopModelInterface $loop, $all = false)
    {
        $this->applyCall(__METHOD__, [ $loop, $all ]);
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function import(LoopModelInterface $loop, $all = false)
    {
        $this->applyCall(__METHOD__, [ $loop, $all ]);
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function swap(LoopModelInterface $loop, $all = false)
    {
        $this->applyCall(__METHOD__, [ $loop, $all ]);
    }
}
