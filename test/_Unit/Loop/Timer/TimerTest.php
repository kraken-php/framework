<?php

namespace Kraken\_Unit\Loop\Timer;

use Kraken\_Unit\Loop\_Mock\LoopModelMock;
use Kraken\Loop\Timer\Timer;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TUnit;
use Prophecy\Prophecy\ObjectProphecy;
use StdClass;

class TimerTest extends TUnit
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var LoopModelInterface
     */
    private $loop;

    /**
     * @param float $interval
     * @param callable $handler
     * @param bool $periodic
     * @param mixed|null $data
     * @return Timer
     */
    public function createTimer($interval, callable $handler, $periodic = false, $data = null)
    {
        $this->prophecy = $this->prophesize(LoopModelMock::class);
        $this->loop = $this->prophecy->reveal();

        return new Timer($this->loop, $interval, $handler, $periodic, $data);
    }

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createTimer(1, function() {});
    }

    /**
     *
     */
    public function testApiConstructor_RespectsMinIntervalConstraint()
    {
        $min = Timer::MIN_INTERVAL;
        $interval = $min / 10;

        $timer = $this->createTimer($interval, function() {});

        $this->assertLessThan($min, $interval);
        $this->assertSame($min, $timer->getInterval());
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $timer = $this->createTimer(1, function() {});
        unset($timer);
    }

    /**
     *
     */
    public function testApiGetLoop_ReturnsLoopModel()
    {
        $timer = $this->createTimer(1, function() {});

        $this->assertSame($this->loop, $timer->getLoop());
    }

    /**
     *
     */
    public function testApiGetInterval_ReturnsInterval()
    {
        $timer = $this->createTimer($interval = 2e-3, function() {});

        $this->assertSame($interval, $timer->getInterval());
    }

    /**
     *
     */
    public function testApiGetCallback_ReturnsCallback()
    {
        $timer = $this->createTimer(1, $callback = function() {});

        $this->assertSame($callback, $timer->getCallback());
    }

    /**
     *
     */
    public function testApiGetData_ReturnsArbitraryData()
    {
        $data = new StdClass;
        $data->a = 'A';
        $data->b = 'B';
        $timer = $this->createTimer(1, function() {}, false, $data);

        $this->assertSame($data, $timer->getData());
    }

    /**
     *
     */
    public function testApiSetData_SetsArbitraryData()
    {
        $data = new StdClass;
        $data->a = 'A';
        $data->b = 'B';
        $timer = $this->createTimer(1, function() {});

        $this->assertSame(null, $timer->getData());
        $timer->setData($data);
        $this->assertSame($data, $timer->getData());
    }

    /**
     *
     */
    public function testApiIsPeriodic_ReturnsFlase_ForNonPeriodicTimers()
    {
        $timer = $this->createTimer(1, function() {});

        $this->assertFalse($timer->isPeriodic());
    }

    /**
     *
     */
    public function testApiIsPeriodic_ReturnsTrue_ForPeriodicTimers()
    {
        $timer = $this->createTimer(1, function() {}, true);

        $this->assertTrue($timer->isPeriodic());
    }

    /**
     *
     */
    public function testApiIsActive_CallsMethodOnModel()
    {
        $timer = $this->createTimer(1, function() {});
        $bool = true;

        $this->prophecy->isTimerActive($timer)->willReturn($bool)->shouldBeCalledTimes(1);
        $this->assertSame($bool, $timer->isActive());
    }

    /**
     *
     */
    public function testApiCancel_CallsMethodOnModel()
    {
        $timer = $this->createTimer(1, function() {});

        $this->prophecy->cancelTimer($timer)->shouldBeCalledTimes(1);
        $timer->cancel();
    }
}
