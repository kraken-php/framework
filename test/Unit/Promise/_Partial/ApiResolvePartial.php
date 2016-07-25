<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\Promise;
use Kraken\Promise\DeferredInterface;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Test\Unit\TestCase;
use Exception;
use StdClass;

trait ApiResolvePartial
{
    /**
     * @return DeferredInterface
     */
    abstract public function createDeferred();

    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiResolve_ResolvesPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred
            ->getPromise()
            ->then($mock);

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiResolve_ResolvesPromise_WithPromisedValue()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred
            ->getPromise()
            ->then($mock);

        $deferred
            ->resolve(Promise::doResolve(1));
    }

    /**
     *
     */
    public function testApiResolve_RejectsPromise_WhenResolvedWithRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $mock,
                $test->expectCallableNever()
            );

        $deferred
            ->resolve(Promise::doReject(1));
    }

    /**
     *
     */
    public function testApiResolve_ForwardsValue_WhenCallbackIsNull()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred
            ->getPromise()
            ->then(
                null,
                $test->expectCallableNever(),
                $test->expectCallableNever()
            )
            ->then(
                $mock,
                $test->expectCallableNever(),
                $test->expectCallableNever()
            );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiResolve_MakesPromiseImmutable()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred
            ->getPromise()
            ->then(
                function($value) use($deferred) {
                    $deferred->resolve(3);
                    return $value;
                }
            )
            ->then(
                $mock,
                $test->expectCallableNever(),
                $test->expectCallableNever()
            );

        $deferred->resolve(1);
        $deferred->resolve(2);
    }

    /**
     *
     */
    public function testApiResolve_InvokesDoneFulfillmentHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $test->assertNull(
            $deferred->getPromise()->done($mock)
        );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_FromFulfillmentHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $test->assertNull(
            $deferred->getPromise()->done(function() {
                throw new Exception('UnhandledRejectionException');
            })
        );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenFulfillmentHandlerRejects()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);
        $test->assertNull(
            $deferred->getPromise()->done(function() {
                return Promise::doReject();
            })
        );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred
            ->getPromise()
            ->always($test->expectCallableOnce())
            ->then($mock);

        $deferred
            ->resolve($value);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue_WhenHandlerReturnsNonPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred
            ->getPromise()
            ->always(function () {
                return 1;
            })
            ->then($mock);

        $deferred
            ->resolve($value);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue_WhenHandlerReturnsPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred
            ->getPromise()
            ->always(function() {
                return Promise::doResolve(1);
            })
            ->then($mock);

        $deferred
            ->resolve($value);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerThrows_AfterResolved()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception('Reason');

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred
            ->getPromise()
            ->always(function() use($exception) {
                throw $exception;
            })
            ->then(
                null,
                $mock
            );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerRejects_AfterResolved()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred
            ->getPromise()
            ->always(function() use($exception) {
                return Promise::doReject($exception);
            })
            ->then(
                null,
                $mock
            );

        $deferred
            ->resolve(1);
    }

    /**
     *
     */
    public function testApiResolve_ReturnsFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred
            ->getPromise()
            ->resolve()
            ->then(
                $test->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiResolve_Returns_FromFulfillmentHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = 5;

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred
            ->getPromise()
            ->spread(
                function() use($value) {
                    return Promise::doResolve($value);
                }
            )
            ->then(
                $mock
            );

        $deferred
            ->resolve();
    }
}
