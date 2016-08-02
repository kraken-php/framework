<?php

namespace Kraken\_Unit\Loop\Timer;

use Kraken\_Unit\Loop\_Mock\LoopModelMock;
use Kraken\Loop\Timer\Timer;
use Kraken\Loop\Timer\TimerCollection;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TUnit;

class TimerCollectionTest extends TUnit
{
    /**
     * @var LoopModelInterface
     */
    private $loop;

    /**
     * @param TimerInterface[] $timers
     * @return TimerCollection
     */
    public function createTimerCollection($timers = [])
    {
        return new TimerCollection($timers);
    }

    /**
     * @param float $interval
     * @param callable|null $handler
     * @param bool $periodic
     * @param mixed|null $data
     * @return TimerInterface
     */
    public function createTimer($interval = 1.0, callable $handler = null, $periodic = false, $data = null)
    {
        if (!isset($this->loop))
        {
            $this->loop = new LoopModelMock();
        }

        $handler = $handler === null ? function() {} : $handler;

        return new Timer($this->loop, $interval, $handler, $periodic, $data);
    }

    /**
     *
     */
    public function testApiConstructor_AllowsEmptyTimerList()
    {
        $collection = $this->createTimerCollection();
    }

    /**
     *
     */
    public function testApiConstructor_AllowsNonEmptyTimerList()
    {
        $timers = [
            $t1 = $this->createTimer(),
            $t2 = $this->createTimer()
        ];
        $collection = $this->createTimerCollection($timers);

        $this->assertSame($timers, $collection->getTimers());
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $collection = $this->createTimerCollection();
        unset($collection);
    }

    /**
     *
     */
    public function testApiGetTimers_ReturnsTimers()
    {
        $timers = [
            't1' => $t1 = $this->createTimer(),
            't2' => $t2 = $this->createTimer()
        ];
        $collection = $this->createTimerCollection($timers);

        $this->assertSame($timers, $collection->getTimers());
    }

    /**
     *
     */
    public function testApiExistsTimer_ReturnsFalse_WhenTimerDoesNotExist()
    {
        $collection = $this->createTimerCollection();
        $name = 'name';

        $this->assertFalse($collection->existsTimer($name));
    }

    /**
     *
     */
    public function testApiExistsTimer_ReturnsTrue_WhenTimerDoesExist()
    {
        $tname  = 'tname';
        $timers = [
            $tname => $this->createTimer()
        ];
        $collection = $this->createTimerCollection($timers);

        $this->assertTrue($collection->existsTimer($tname));
    }

    /**
     *
     */
    public function testApiAddTimer_AddsTimer()
    {
        $collection = $this->createTimerCollection();
        $tname  = 'tname';

        $this->assertFalse($collection->existsTimer($tname));
        $collection->addTimer($tname, $this->createTimer());
        $this->assertTrue($collection->existsTimer($tname));
    }

    /**
     *
     */
    public function testApiGetTimer_ReturnsTimer_WhenTimerDoesExist()
    {
        $collection = $this->createTimerCollection();
        $tname = 'tname';
        $timer = $this->createTimer();

        $collection->addTimer($tname, $timer);
        $this->assertSame($timer, $collection->getTimer($tname));
    }

    /**
     *
     */
    public function testApiGetTimer_ReturnsNull_WhenTimerDoesNotExist()
    {
        $collection = $this->createTimerCollection();
        $tname = 'tname';

        $this->assertSame(null, $collection->getTimer($tname));
    }

    /**
     *
     */
    public function testApiRemoveTimer_RemovesTimer_WhenTimerDoesExist()
    {
        $collection = $this->createTimerCollection();
        $tname = 'tname';
        $timer = $this->createTimer();

        $collection->addTimer($tname, $timer);
        $this->assertTrue($collection->existsTimer($tname));

        $collection->removeTimer($tname);
        $this->assertFalse($collection->existsTimer($tname));
    }

    /**
     *
     */
    public function testApiRemoveTimer_DoesNothing_WhenTimerDoesNotExist()
    {
        $collection = $this->createTimerCollection();
        $tname = 'tname';

        $this->assertFalse($collection->existsTimer($tname));
        $collection->removeTimer($tname);
        $this->assertFalse($collection->existsTimer($tname));
    }
}
