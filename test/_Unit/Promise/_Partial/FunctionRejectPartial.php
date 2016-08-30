<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Promise\PromiseRejected;
use Kraken\Test\TUnit;

trait FunctionRejectPartial
{
    /**
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();
    
    /**
     *
     */
    public function testApiDoReject_RejectsPromise_WithImmediateValue()
    {
        $test = $this->getTest();

        $expected = 123;
        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doReject($expected)
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDoReject_RejectsPromise_WithPromisedValue()
    {
        $test = $this->getTest();

        $expected = 123;
        $resolved = new PromiseFulfilled($expected);

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doReject($resolved)
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDoReject_RejectsPromise_WithRejectedPromise()
    {
        $test = $this->getTest();

        $expected = 123;
        $resolved = new PromiseRejected($expected);

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doReject($resolved)
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDoReject_ReturnsPromise()
    {
        $test = $this->getTest();
        $test->assertInstanceOf(PromiseInterface::class, Promise::doReject(1));
    }
}
