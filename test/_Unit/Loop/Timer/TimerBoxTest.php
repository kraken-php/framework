<?php

namespace Kraken\_Unit\Loop\Timer;

use Kraken\_Unit\Loop\_Mock\LoopModelMock;
use Kraken\Loop\Timer\Timer;
use Kraken\Loop\Timer\TimerBox;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TUnit;

class TimerBoxTest extends TUnit
{
    /**
     * @var LoopModelInterface
     */
    private $loop;

    /**
     * @return TimerBox
     */
    public function createTimerBox()
    {
        return new TimerBox();
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
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createTimerBox();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $timers = $this->createTimerBox();
        unset($timers);
    }

    /**
     *
     */
    public function testApiUpdateTime_UpdatesTime()
    {
        $timers = $this->createTimerBox();

        $time1 = $timers->getTime();
        usleep(1e3);
        $time2 = $timers->updateTime();

        $this->assertNotSame($time1, $time2);
    }

    /**
     *
     */
    public function testApiGetTime_ReturnsTime()
    {
        $timers = $this->createTimerBox();
        $eps = 1e-3;

        $time1 = microtime(true);
        $time2 = $timers->getTime();

        $this->assertLessThan($eps, $time2 - $time1);
    }

    /**
     *
     */
    public function testApiContains_ReturnsFalse_WhenTimerDoesNotExist()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $this->assertFalse($timers->contains($timer));
    }

    /**
     *
     */
    public function testApiContains_ReturnsTrue_WhenTimerDoesExist()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $this->assertFalse($timers->contains($timer));
        $timers->add($timer);
        $this->assertTrue($timers->contains($timer));
    }

    /**
     *
     */
    public function testApiAdd_AddsTimer()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $this->assertFalse($timers->contains($timer));
        $timers->add($timer);
        $this->assertTrue($timers->contains($timer));
    }

    /**
     *
     */
    public function testApiRemove_DoesNothing_WhenTimerDoesNotExist()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $this->assertFalse($timers->contains($timer));
        $timers->remove($timer);
        $this->assertFalse($timers->contains($timer));
    }

    /**
     *
     */
    public function testApiRemove_RemovesTimer_WhenTimerDoesExist()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $timers->add($timer);
        $this->assertTrue($timers->contains($timer));

        $timers->remove($timer);
        $this->assertFalse($timers->contains($timer));
    }

    /**
     *
     */
    public function testApiGetFirst_ReturnsNull_WhenBoxIsEmpty()
    {
        $timers = $this->createTimerBox();

        $this->assertSame(null, $timers->getFirst());
    }

    /**
     *
     */
    public function testApiIsEmpty_ReturnsFalse_WhenBoxIsFilled()
    {
        $timers = $this->createTimerBox();
        $timer  = $this->createTimer();

        $timers->add($timer);

        $this->assertFalse($timers->isEmpty());
    }

    /**
     *
     */
    public function testApiIsEmpty_ReturnsTrue_WhenBoxIsEmpty()
    {
        $timers = $this->createTimerBox();

        $this->assertTrue($timers->isEmpty());
    }
}
