<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Promise\PromiseRejected;
use Kraken\Test\TUnit;

trait FunctionResolvePartial
{
    /**
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiDoResolve_ResolvesPromise_WithImmediateValue()
    {
        $test = $this->getTest();

        $expected = 123;
        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doResolve($expected)
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiDoResolve_ResolvesPromsie_WithPromisedValue()
    {
        $test = $this->getTest();

        $expected = 123;
        $resolved = new PromiseFulfilled($expected);

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doResolve($resolved)
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiDoResolve_RejectsPromise_WithRejectedPromise()
    {
        $test = $this->getTest();

        $expected = 123;
        $resolved = new PromiseRejected($expected);

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($expected));

        Promise::doResolve($resolved)
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDoResolve_SupportsDeepNestingInPromiseChains()
    {
        $test = $this->getTest();

        $d = new Deferred();
        $d->resolve(false);
        $result = Promise::doResolve(Promise::doResolve($d->getPromise()->then(function($val) {
            $d = new Deferred();
            $d->resolve($val);
            $identity = function($val) {
                return $val;
            };
            return Promise::doResolve($d->getPromise()->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(true));

        $result
            ->then($mock);
    }

    /**
     *
     */
    public function testApiDoResolve_ReturnsPromise()
    {
        $test = $this->getTest();
        $test->assertInstanceOf(PromiseInterface::class, Promise::doResolve(1));
    }
}
