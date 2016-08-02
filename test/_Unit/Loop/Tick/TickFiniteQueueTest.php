<?php

namespace Kraken\_Unit\Loop\Tick;

use Kraken\_Unit\Loop\_Mock\LoopModelMock;
use Kraken\_Unit\Loop\_Mock\Timer\TickFiniteQueueMock;
use Kraken\Test\TUnit;

class TickFiniteQueueTest extends TUnit
{
    /**
     * @return TickFiniteQueueMock
     */
    public function createTickQueue()
    {
        $prophecy = $this->prophesize(LoopModelMock::class);

        $prophecy->isRunning()->willReturn(true);

        return new TickFiniteQueueMock($prophecy->reveal());
    }

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createTickQueue();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $queue = $this->createTickQueue();
        unset($queue);
    }

    /**
     *
     */
    public function testApiAdd_AddsListener()
    {
        $queue = $this->createTickQueue();

        $queue->add($c1 = function() {});
        $queue->add($c2 = function() {});
        $queue->add($c3 = function() {});

        $spl = $queue->getQueue();
        $got = [];

        while (!$spl->isEmpty())
        {
            $got[] = $spl->dequeue();
        }

        $this->assertSame([ $c1, $c2, $c3 ], $got);
    }

    /**
     *
     */
    public function testApiTick_InvokesOnlyCallbacksThatWereOnQueueBeforeTick()
    {
        $queue = $this->createTickQueue();
        $str = '';

        $queue->add(function() use(&$str) {
            $str .= 'A';
        });
        $queue->add(function() use(&$str, $queue) {
            $queue->add(function() use(&$str) {
                $str .= 'D';
            });
            $str .= 'B';
        });
        $queue->add(function() use(&$str, $queue) {
            $str .= 'C';
            $queue->add(function() use(&$str) {
                $str .= 'E';
            });
        });

        $queue->tick();

        $this->assertSame('ABC', $str);
    }

    /**
     *
     */
    public function testApiIsEmpty_ReturnsTrue_WhenQueueIsEmpty()
    {
        $queue = $this->createTickQueue();

        $this->assertTrue($queue->isEmpty());
    }

    /**
     *
     */
    public function testApiIsEmpty_ReturnsFalse_WhenQueueIsNotEmpty()
    {
        $queue = $this->createTickQueue();

        $queue->add(function() {});
        $this->assertFalse($queue->isEmpty());
    }
}
