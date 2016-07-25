<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\_Unit\TestCase;

trait FunctionReducePartial
{
    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiReduce_ReducesPrimitiveInputValues_WithoutInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(6));

        Promise::reduce(
            [ 1, 2, 3 ],
            $this->plus()
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesPrimitiveInputValues_WithInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(7));

        Promise::reduce(
            [ 1, 2, 3 ],
            $this->plus(),
            1
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesPrimitiveInputValues_WithInitialPromise()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(7));

        Promise::reduce(
            [ 1, 2, 3 ],
            $this->plus(),
            Promise::doResolve(1)
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesPromisedInputValues_WithoutInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(6));

        Promise::reduce(
            [ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ],
            $this->plus()
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesPromisedInputValues_WithInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(7));

        Promise::reduce(
            [ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ],
            $this->plus(),
            1
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesPromisedInputValues_WithInitialPromise()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(7));

        Promise::reduce(
            [ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ],
            $this->plus(),
            Promise::doResolve(1)
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesEmptyInputValues_WithInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        Promise::reduce(
            [],
            $this->plus(),
            1
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesEmptyInputValues_WithInitialPromise()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        Promise::reduce(
            [],
            $this->plus(),
            Promise::doResolve(1)
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_RejectsPromise_WhenInputContainsRejection()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        Promise::reduce(
            [ Promise::doResolve(1), Promise::doReject(2), Promise::doResolve(3) ],
            $this->plus(),
            Promise::doResolve(1)
        )->then(
            $test->expectCallableNever(),
            $mock
        );
    }

    /**
     *
     */
    public function testApiReduce_ResolvesPromiseWithNull_WhenInputIsEmptyAndNoInitialValueOrPromiseProvided()
    {
        $test = $this->getTest();

        // Note: this is different from when.js's behavior!
        // In when.Promise::reduce(), this Promise::doRejects with a TypeError exception (following
        // JavaScript's [].Promise::reduce behavior.
        // We're following PHP's array_Promise::reduce behavior and Promise::doResolve with NULL.
        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(null));

        Promise::reduce(
            [],
            $this->plus()
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_AllowsSparseInputValues_WithoutInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(3));

        Promise::reduce(
            [ null, null, 1, null, 1, 1 ],
            $this->plus()
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_AllowsSparseInputValues_WithInitialValue()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(4));

        Promise::reduce(
            [ null, null, 1, null, 1, 1 ],
            $this->plus(),
            1
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ReducesInInputOrder()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo('123'));

        Promise::reduce(
            [ 1, 2, 3 ],
            $this->append(),
            ''
        )->then($mock);
    }

    /**
     *
     */
    public function testApiReduce_ProvidesCorrectBasisValue()
    {
        $test = $this->getTest();

        $insertIntoArray = function($arr, $val, $i) {
            $arr[$i] = $val;
            return $arr;
        };
        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1, 2, 3 ]));

        Promise::reduce(
            [ $d1->getPromise(), $d2->getPromise(), $d3->getPromise() ],
            $insertIntoArray,
            []
        )->then($mock);

        $d3->resolve(3);
        $d1->resolve(1);
        $d2->resolve(2);
    }

    /**
     *
     */
    public function testApiReduce_CancelsPromisedInputValues()
    {
        $test = $this->getTest();

        $p1 = (new Deferred)->getPromise();
        $p2 = (new Deferred)->getPromise();

        $p1
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        $p2
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        Promise::reduce(
            [ $p1, $p2 ],
            $this->plus(),
            1
        )->cancel();
    }

    /**
     * @return callable
     */
    protected function plus()
    {
        return function ($sum, $val) {
            return $sum + $val;
        };
    }

    /**
     * @return callable
     */
    protected function append()
    {
        return function ($sum, $val) {
            return $sum . $val;
        };
    }
}
