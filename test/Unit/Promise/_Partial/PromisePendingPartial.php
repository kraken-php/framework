<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\TestCase;

trait PromisePendingPartial
{
    /**
     * @return DeferredInterface
     */
    abstract public function createDeferred();

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function createPromiseMock($methods = []);

    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiThen_ReturnsPromise_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertInstanceOf(PromiseInterface::class, $deferred->getPromise()->then());
    }

    /**
     *
     */
    public function testApiThen_ReturnsPromise_UsingSetOfNulls_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertInstanceOf(PromiseInterface::class, $deferred->getPromise()->then(null, null, null));
    }

    /**
     *
     */
    public function testApiDone_ReturnsNull_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertNull($deferred->getPromise()->done());
    }

    /**
     *
     */
    public function testApiDone_ReturnsNull_UsingSetOfNulls_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertNull($deferred->getPromise()->done(null, null, null));
    }
    /**
     *
     */
    public function testApiAlways_ReturnsPromise_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertInstanceOf(PromiseInterface::class, $deferred->getPromise()->always(function () {}));
    }

    /**
     *
     */
    public function testApiIsPending_ReturnsTrue_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertTrue($deferred->getPromise()->isPending());
    }

    /**
     *
     */
    public function testApiIsFulfilled_ReturnsFalse_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertFalse($deferred->getPromise()->isFulfilled());
    }

    /**
     *
     */
    public function testApiIsRejected_ReturnsFalse_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertFalse($deferred->getPromise()->isRejected());
    }

    /**
     *
     */
    public function testApiIsCancelled_ReturnsFalse_ForPendingPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertFalse($deferred->getPromise()->isCancelled());
    }

    /**
     *
     */
    public function testApiSuccess_CallsThenWithValidArguments_ForPendingPromise()
    {
        $test = $this->getTest();
        $callable = function() {};

        $promise = $this->createPromiseMock([ 'then' ]);
        $promise
            ->expects($test->once())
            ->method('then')
            ->with($callable, null, null);

        $promise
            ->success($callable);
    }

    /**
     *
     */
    public function testApiFailure_CallsThenWithValidArguments_ForPendingPromise()
    {
        $test = $this->getTest();
        $callable = function() {};

        $promise = $this->createPromiseMock([ 'then' ]);
        $promise
            ->expects($test->once())
            ->method('then')
            ->with(null, $callable, null);

        $promise
            ->failure($callable);
    }

    /**
     *
     */
    public function testApiAbort_CallsThenWithValidArguments_ForPendingPromise()
    {
        $test = $this->getTest();
        $callable = function() {};

        $promise = $this->createPromiseMock([ 'then' ]);
        $promise
            ->expects($test->once())
            ->method('then')
            ->with(null, null, $callable);

        $promise
            ->abort($callable);
    }
}
