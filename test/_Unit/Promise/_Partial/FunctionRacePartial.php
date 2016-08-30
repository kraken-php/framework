<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;

trait FunctionRacePartial
{
    /**
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiRace_ResolvesPromise_WithEmptyInputArray()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(null));

        Promise::race([])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiRace_ResolvesPromise_WithPrimitiveInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        Promise::race([ 1, 2, 3 ])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiRace_ResolvesPromise_WithPromisedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        Promise::race([ $d1->getPromise(), $d2->getPromise(), $d3->getPromise() ])
            ->then(
                $mock
            );

        $d2->resolve(2);
        $d1->resolve(1);
        $d3->resolve(3);
    }

    /**
     *
     */
    public function testApiRace_RejectsPromise_IfAnyInputPromisesReject()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        Promise::race([ $d1->getPromise(), $d2->getPromise(), $d3->getPromise() ])
            ->then(
                $test->expectCallableNever(),
                $mock
            );

        $d2->reject(2);
        $d1->resolve(1);
        $d3->resolve(3);
    }

    /**
     *
     */
    public function testApiRace_CancelsPromisedInputValues()
    {
        $test = $this->getTest();

        $mock1 = $test->getMock(PromiseInterface::class);
        $mock1
            ->expects($test->once())
            ->method('cancel');

        $mock2 = $test->getMock(PromiseInterface::class);
        $mock2
            ->expects($test->once())
            ->method('cancel');

        Promise::race([ $mock1, $mock2 ])
            ->cancel();
    }

    /**
     *
     */
    public function testApiRace_CancelsPendingInputArrayPromises_IfOnePromiseFulfills()
    {
        $test = $this->getTest();

        $mock1 = $test->createCallableMock();
        $mock1
            ->expects($test->never())
            ->method('__invoke');

        $deferred = new Deferred($mock1);

        $deferred->resolve();

        $mock2 = $test->getMock(PromiseInterface::class);
        $mock2
            ->expects($test->once())
            ->method('cancel');

        Promise::race([ $deferred->getPromise(), $mock2 ])
            ->cancel();
    }

    /**
     *
     */
    public function testApiRace_CancelsPendingInputArrayPromises_IfOnePromiseRejects()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->never())
            ->method('__invoke');

        $deferred = new Deferred($mock);

        $deferred->reject();

        $mock2 = $test->getMock(PromiseInterface::class);
        $mock2
            ->expects($test->once())
            ->method('cancel');

        Promise::race([ $deferred->getPromise(), $mock2 ])
            ->cancel();
    }
}
