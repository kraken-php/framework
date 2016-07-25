<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Promise;
use Kraken\_Unit\TestCase;

trait FunctionAllPartial
{
    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiAll_ResolvesPromise_WithEmptyInputArray()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([]));

        Promise::all([])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAll_ResolvesPromise_WithPrimitiveInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1, 2, 3 ]));

        Promise::all([ 1, 2, 3 ])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAll_ResolvesPromise_WithPromisedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 1, 2, 3 ]));

        Promise::all([ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ])
            ->then(
                $mock
            );
    }

    /**
     *
     */
    public function testApiAll_RejectsPromise_IfAnyInputPromisesReject()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        Promise::all([ Promise::doResolve(1), Promise::doReject(2), Promise::doResolve(3) ])
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }
}
