<?php

namespace Kraken\Loop\Model;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Tick\ContinousTickQueue;
use Kraken\Loop\Tick\FiniteTickQueue;
use Kraken\Loop\Timer\Timer;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\Timer\Timers;
use Kraken\Loop\LoopModelInterface;

class StreamSelectLoop implements LoopModelInterface
{
    /**
     * @var int
     */
    const MICROSECONDS_PER_SECOND = 1000000;

    /**
     * @var ContinousTickQueue
     */
    protected $startTickQueue;

    /**
     * @var ContinousTickQueue
     */
    protected $stopTickQueue;

    /**
     * @var ContinousTickQueue
     */
    protected $nextTickQueue;

    /**
     * @var FiniteTickQueue
     */
    protected $futureTickQueue;

    protected $flowController;

    /**
     * @var
     */
    protected $timers;
    protected $readStreams = [];
    protected $readListeners = [];
    protected $writeStreams = [];
    protected $writeListeners = [];
    protected $running;

    public function __construct()
    {
        $this->startTickQueue = new ContinousTickQueue($this);
        $this->stopTickQueue = new ContinousTickQueue($this);
        $this->nextTickQueue = new ContinousTickQueue($this);
        $this->futureTickQueue = new FiniteTickQueue($this);
        $this->timers = new Timers();
        $this->flowController = new FlowController();
    }

    public function isRunning()
    {
        return isset($this->flowController->isRunning) ? $this->flowController->isRunning : false;
    }

    /**
     * {@inheritdoc}
     */
    public function addReadStream($stream, callable $listener)
    {
        $key = (int) $stream;

        if (!isset($this->readStreams[$key]))
        {
            $this->readStreams[$key] = $stream;
            $this->readListeners[$key] = $listener;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addWriteStream($stream, callable $listener)
    {
        $key = (int) $stream;

        if (!isset($this->writeStreams[$key]))
        {
            $this->writeStreams[$key] = $stream;
            $this->writeListeners[$key] = $listener;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeReadStream($stream)
    {
        $key = (int) $stream;

        unset(
            $this->readStreams[$key],
            $this->readListeners[$key]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeWriteStream($stream)
    {
        $key = (int) $stream;

        unset(
            $this->writeStreams[$key],
            $this->writeListeners[$key]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeStream($stream)
    {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function addTimer($interval, callable $callback)
    {
        $timer = new Timer($this, $interval, $callback, false);

        $this->timers->add($timer);

        return $timer;
    }

    /**
     * {@inheritdoc}
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        $timer = new Timer($this, $interval, $callback, true);

        $this->timers->add($timer);

        return $timer;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->timers->cancel($timer);
    }

    /**
     * {@inheritdoc}
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->timers->contains($timer);
    }

    /**
     * {@inheritdoc}
     */
    public function startTick(callable $listener)
    {
        $this->startTickQueue->add($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function stopTick(callable $listener)
    {
        $this->stopTickQueue->add($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTick(callable $listener)
    {
        $this->nextTickQueue->add($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function afterTick(callable $listener)
    {
        $this->futureTickQueue->add($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function tick()
    {
        $this->flowController->isRunning = true;

        $this->nextTickQueue->tick();
        $this->futureTickQueue->tick();
        $this->timers->tick();
        $this->waitForStreamActivity(0);

        $this->flowController->isRunning = false;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->flowController->isRunning)
        {
            return;
        }

        // TODO delete this
        $this->addPeriodicTimer(1, function() {
            usleep(1);
        });

        $this->flowController->isRunning = true;
        $this->startTickQueue->tick();

        while ($this->flowController->isRunning)
        {
            $this->nextTickQueue->tick();

            $this->futureTickQueue->tick();

            $this->timers->tick();

            // Next-tick or future-tick queues have pending callbacks ...
            if (!$this->flowController->isRunning || !$this->nextTickQueue->isEmpty() || !$this->futureTickQueue->isEmpty())
            {
                $timeout = 0;
            }
            // There is a pending timer, only block until it is due ...
            else if ($scheduledAt = $this->timers->getFirst())
            {
                $timeout = $scheduledAt - $this->timers->getTime();
                $timeout = ($timeout < 0) ? 0 : $timeout * self::MICROSECONDS_PER_SECOND;
            }
            // The only possible event is stream activity, so wait forever ...
            else if ($this->readStreams || $this->writeStreams)
            {
                $timeout = null;
            }
            // There's nothing left to do ...
            else
            {
                break;
            }

            $this->waitForStreamActivity($timeout);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if (!$this->flowController->isRunning)
        {
            return;
        }

        $this->stopTickQueue->tick();
        $this->flowController->isRunning = false;
    }

    /**
     * @param mixed $flowController
     */
    public function setFlowController($flowController)
    {
        $this->flowController = $flowController;
    }

    /**
     * @return FlowController
     */
    public function getFlowController()
    {
        return $this->flowController;
    }

    /**
     * @param bool $all
     * @return LoopModelInterface
     */
    public function flush($all = false)
    {
        $this->stop();
        $loop = new static();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($list as $key=>$val)
        {
            $this->$key = $loop->$key;
        }

        $this->flowController->isRunning = false;

        return $this;
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function export(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($this as $key=>$val)
        {
            $loop->$key = $this->$key;
        }

        return $this;
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function import(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($this as $key=>$val)
        {
            $this->$key = $loop->$key;
        }

        return $this;
    }

    /**
     * @param LoopModelInterface $loop
     * @param bool $all
     * @return LoopModelInterface
     */
    public function swap(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($this as $key=>$val)
        {
            $tmp = $loop->$key;
            $loop->$key = $this->$key;
            $this->$key = $tmp;
        }

        return $this;
    }

    /**
     * Wait/check for stream activity, or until the next timer is due.
     */
    private function waitForStreamActivity($timeout)
    {
        $read  = $this->readStreams;
        $write = $this->writeStreams;

        $this->streamSelect($read, $write, $timeout);

        foreach ($read as $stream)
        {
            $key = (int) $stream;

            if (isset($this->readListeners[$key]))
            {
                $callable = $this->readListeners[$key];
                $callable($stream, $this);
            }
        }

        foreach ($write as $stream)
        {
            $key = (int) $stream;

            if (isset($this->writeListeners[$key]))
            {
                $callable = $this->writeListeners[$key];
                $callable($stream, $this);
            }
        }
    }

    /**
     * Emulate a stream_select() implementation that does not break when passed
     * empty stream arrays.
     *
     * @param array        &$read   An array of read streams to select upon.
     * @param array        &$write  An array of write streams to select upon.
     * @param integer|null $timeout Activity timeout in microseconds, or null to wait forever.
     *
     * @return integer The total number of streams that are ready for read/write.
     */
    private function streamSelect(array &$read, array &$write, $timeout)
    {
        if ($read || $write)
        {
            $except = null;

            return stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
        }

        usleep($timeout);

        return 0;
    }

    /**
     * Get list of properties that can be exported/imported safely.
     *
     * @return array
     */
    private function getTransferableProperties()
    {
        return [
            'nextTickQueue'     => null,
            'futureTickQueue'   => null
        ];
    }
}
