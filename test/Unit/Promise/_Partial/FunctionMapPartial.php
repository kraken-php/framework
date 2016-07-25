<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\TestCase;

trait FunctionMapPartial
{
    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiMap_MapsPrimitiveInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 2, 4, 6 ]));

        Promise::map(
            [ 1, 2, 3 ],
            $this->mapper()
        )
        ->then(
            $mock
        );
    }

    /**
     *
     */
    public function testApiMap_MapsPromisedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 2, 4, 6 ]));

        Promise::map(
            [ Promise::doResolve(1), Promise::doResolve(2), Promise::doResolve(3) ],
            $this->mapper()
        )
        ->then(
            $mock
        );
    }

    /**
     *
     */
    public function testApiMap_MapsMixedInputValues()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 2, 4, 6 ]));

        Promise::map(
            [ 1, Promise::doResolve(2), 3 ],
            $this->mapper()
        )
        ->then(
            $mock
        );
    }

    /**
     *
     */
    public function testApiMap_MapsPrimitiveInputValues_WhenMapperReturnsPromise()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo([ 2, 4, 6 ]));
        
        Promise::map(
            [ 1, 2, 3 ],
            $this->promiseMapper()
        )
        ->then(
            $mock
        );
    }

    /**
     *
     */
    public function testApiMap_RejectsPromise_WhenInputContainsRejection()
    {
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        Promise::map(
            [ Promise::doResolve(1), Promise::doReject(2), Promise::doResolve(3) ],
            $this->mapper()
        )
        ->then(
            $test->expectCallableNever(),
            $mock
        );
    }

    /**
     *
     */
    public function testApiMap_CancelsPromisedInputValues()
    {
        $test = $this->getTest();

        $d1 = (new Deferred())->promise();
        $d2 = (new Deferred())->promise();

        $d1
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        $d2
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        Promise::map(
            [ $d1, $d2 ],
            $this->mapper()
        )
        ->cancel();
    }

    /**
     * @return callable
     */
    protected function mapper()
    {
        return function($val) {
            return $val * 2;
        };
    }

    /**
     * @return callable
     */
    protected function promiseMapper()
    {
        return function($val) {
            return Promise::doResolve($val * 2);
        };
    }
}
