<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\TestCase;
use Kraken\Throwable\Exception\Runtime\UnderflowException;

trait FunctionSomePartial
{
    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiSome_RejectsPromise_WithEmptyInputArray()
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

        Promise::some(
            [],
            1
        )->then(
            $test->expectCallableNever(),
            $mock
        );
    }

    /**
     *
     */
    public function testApiSome_RejectsPromise_WithInputArrayContainingNotEnoughItems()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(
                $test->callback(function($exception){
                    return $exception instanceof UnderflowException &&
                    'Input array must contain at least 4 items but contains only 3 items.' === $exception->getMessage();
                })
            );

        Promise::some(
            [ 1, 2, 3 ],
            4
        )->then(
            $test->expectCallableNever(),
            $mock
        );
    }

    /**
     *
     */
    public function testApiSome_ResolvesPromise_WithPrimitiveInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1, 2 ]));

        Promise::some(
            [ 1, 2, 3 ],
            2
        )->then($mock);
    }

    /**
     *
     */
    public function testApiSome_ResolvesPromise_WithPromisedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1, 2 ]));

        Promise::some(
            [ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ],
            2
        )->then($mock);
    }

    /**
     *
     */
    public function testApiSome_ResolvesPromise_WithSparseInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ null, 1 ]));

        Promise::some(
            [ null, 1, null, 2, 3 ],
            2
        )->then($mock);
    }

    /**
     *
     */
    public function testApiSome_RejectsPromise_IfAnyInputPromiseRejects_BeforeDesiredNumberOfInputsAreResolved()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1 => 2, 2 => 3 ]));

        Promise::some(
            [ Promise::doResolve(1), Promise::doReject(2), Promise::doReject(3) ],
            2
        )->then(
            $test->expectCallableNever(),
            $mock
        );
    }

    /**
     *
     */
    public function testApiSome_ResolvesPromise_WithEmptyInputArray_IfHowManyIsLessThanOne()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([]));

        Promise::some(
            [ 1 ],
            0
        )->then($mock);
    }

    /**
     *
     */
    public function testApiSome_CancelsPromisedInputValues()
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

        Promise::some(
            [ $mock1, $mock2 ],
            1
        )->cancel();
    }

    /**
     *
     */
    public function testApiSome_CancelsPendingInputArrayPromises_IfEnoughPromisesFulfill()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->never())
            ->method('__invoke');

        $deferred = new Deferred($mock);
        $deferred->resolve();

        $mock2 = $test->getMock(PromiseInterface::class);
        $mock2
            ->expects($test->once())
            ->method('cancel');

        Promise::some(
            [ $deferred->promise(), $mock2 ],
            1
        );
    }

    /**
     *
     */
    public function testApiSome_CancelsPendingInputArrayPromises_IfEnoughPromisesReject()
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

        Promise::some(
            [ $deferred->promise(), $mock2 ],
            2
        );
    }
}
