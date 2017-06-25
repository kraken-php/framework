<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Dazzle\Throwable\Exception\Runtime\UnderflowException;
use Kraken\Test\TUnit;

trait FunctionAnyPartial
{
    /**
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiAny_RejectsPromise_WithEmptyInputArray()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(
                $test->callback(function($exception) {
                    return $exception instanceof UnderflowException &&
                    'Input array must contain at least 1 items but contains only 0 items.' === $exception->getMessage();
                })
            );

        Promise::any([])
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiAny_ResolvesPromise_WithPrimitiveInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        Promise::any([1, 2, 3])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAny_ResolvesPromise_WithPromisedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        Promise::any([ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAny_RejectsPromise_WithRejectedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([0 => 1, 1 => 2, 2 => 3]));

        Promise::any([ Promise::doReject(1), Promise::doReject(2), Promise::doReject(3) ])
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiAny_ResolvesPromise_IfAnyInputPromisesResolve()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        Promise::any([ Promise::doReject(1), Promise::doResolve(2), Promise::doReject(3) ])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAny_DoesNotRelyOnArryIndexes_WhenUnwrappingToSingleResolutionValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();

        Promise::any([ 'abc' => $d1->getPromise(), 1 => $d2->getPromise() ])
            ->then(
                $mock
            );

        $d2->resolve(2);
        $d1->resolve(1);
    }

    /**
     *
     */
    public function testApiCancel_CancelsInputArrayPromises()
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

        Promise::any([ $mock1, $mock2 ])
            ->cancel();
    }

    /**
     *
     */
    public function testApiCancel_CancelsPendingInputArrayPromises_IfOnePromiseFulfills()
    {
        $test = $this->getTest();

        $deferred = new Deferred();
        $deferred->resolve();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->never())
            ->method('__invoke');

        $promise = $deferred->getPromise();
        $promise
            ->then(
                null,
                null,
                $mock
            );

        $mock2 = $test->getMock(PromiseInterface::class);
        $mock2
            ->expects($test->once())
            ->method('cancel');

        Promise::some(
            [ $promise, $mock2 ],
            1
        )->cancel();
    }
}
