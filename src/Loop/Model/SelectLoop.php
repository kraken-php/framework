<?php

namespace Kraken\Loop\Model;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Tick\TickContinousQueue;
use Kraken\Loop\Tick\TickFiniteQueue;
use Kraken\Loop\Timer\Timer;
use Kraken\Loop\Timer\TimerBox;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopModelInterface;

class SelectLoop implements LoopModelInterface
{
    /**
     * @var int
     */
    const MICROSECONDS_PER_SECOND = 1e6;

    /**
     * @var TickContinousQueue
     */
    protected $startTickQueue;

    /**
     * @var TickContinousQueue
     */
    protected $stopTickQueue;

    /**
     * @var TickContinousQueue
     */
    protected $nextTickQueue;

    /**
     * @var TickFiniteQueue
     */
    protected $futureTickQueue;

    /**
     * @var FlowController
     */
    protected $flowController;

    /**
     * @var TimerBox
     */
    protected $timers;

    /**
     * @var resource[]
     */
    protected $readStreams = [];

    /**
     * @var callable[]
     */
    protected $readListeners = [];

    /**
     * @var resource[]
     */
    protected $writeStreams = [];

    /**
     * @var callable[]
     */
    protected $writeListeners = [];

    /**
     *
     */
    public function __construct()
    {
        $this->startTickQueue = new TickContinousQueue($this);
        $this->stopTickQueue = new TickContinousQueue($this);
        $this->nextTickQueue = new TickContinousQueue($this);
        $this->futureTickQueue = new TickFiniteQueue($this);
        $this->flowController = new FlowController();
        $this->timers = new TimerBox();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->startTickQueue);
        unset($this->stopTickQueue);
        unset($this->nextTickQueue);
        unset($this->futureTickQueue);
        unset($this->flowController);
        unset($this->timers);
        unset($this->readStreams);
        unset($this->readListeners);
        unset($this->writeStreams);
        unset($this->writeListeners);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRunning()
    {
        return isset($this->flowController->isRunning) ? $this->flowController->isRunning : false;
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function removeStream($stream)
    {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addTimer($interval, callable $callback)
    {
        $timer = new Timer($this, $interval, $callback, false);

        $this->timers->add($timer);

        return $timer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        $timer = new Timer($this, $interval, $callback, true);

        $this->timers->add($timer);

        return $timer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancelTimer(TimerInterface $timer)
    {
        if (isset($this->timers))
        {
            $this->timers->remove($timer);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->timers->contains($timer);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStart(callable $listener)
    {
        $this->startTickQueue->add($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStop(callable $listener)
    {
        $this->stopTickQueue->add($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onBeforeTick(callable $listener)
    {
        $this->nextTickQueue->add($listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onAfterTick(callable $listener)
    {
        $this->futureTickQueue->add($listener);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function start()
    {
        if ($this->flowController->isRunning)
        {
            return;
        }

        // TODO KRF-107
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function setFlowController($flowController)
    {
        $this->flowController = $flowController;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFlowController()
    {
        return $this->flowController;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function erase($all = false)
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
     * @override
     * @inheritDoc
     */
    public function export(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($list as $key=>$val)
        {
            $loop->$key = $this->$key;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function import(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($list as $key=>$val)
        {
            $this->$key = $loop->$key;
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function swap(LoopModelInterface $loop, $all = false)
    {
        $this->stop();
        $loop->stop();

        $list = $all === true ? $this : $this->getTransferableProperties();
        foreach ($list as $key=>$val)
        {
            $tmp = $loop->$key;
            $loop->$key = $this->$key;
            $this->$key = $tmp;
        }

        return $this;
    }

    /**
     * Wait/check for stream activity, or until the next timer is due.
     *
     * @param float $timeout
     */
    private function waitForStreamActivity($timeout)
    {
        $read  = $this->readStreams;
        $write = $this->writeStreams;

        if ($this->streamSelect($read, $write, $timeout) === false)
        {
            return;
        }

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
     * Emulate a stream_select() implementation that does not break when passed empty stream arrays.
     *
     * @param array &$read
     * @param array &$write
     * @param integer|null $timeout
     *
     * @return integer The total number of streams that are ready for read/write.
     */
    private function streamSelect(array &$read, array &$write, $timeout)
    {
        if ($read || $write)
        {
            $except = null;

            return @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
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
            'futureTickQueue'   => null,
            'flowController'    => null
        ];
    }
}
