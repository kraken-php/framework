<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Exception;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\TestCase;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use stdClass;

trait PromiseFulfilledPartial
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
    public function testCasePromiseFulfilled_IsImmutable()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->resolve(1);
        $deferred->resolve(2);

        $deferred
            ->getPromise()
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testCasePromiseFulfilled_InvokesNewlyAddedCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsValue_WhenCallbackIsNull()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->resolve(1);
        $deferred
            ->getPromise()
            ->then(
                null,
                $test->expectCallableNever()
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsValue_ToNextCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->then(
                function($val) {
                    return $val + 1;
                },
                $test->expectCallableNever()
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsPromisedValue_ToNextCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->then(
                function($val) {
                    return Promise::doResolve($val + 1);
                },
                $test->expectCallableNever()
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsRejection_ToNextCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->then(
                function($val) {
                    return Promise::doReject($val + 1);
                },
                $test->expectCallableNever()
            )
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_SwitchesFromCallbacksToErrbacks_WhenCallbackThrows()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock1 = $test->createCallableMock();
        $mock1
            ->expects($test->once())
            ->method('__invoke')
            ->will($test->throwException($exception));

        $mock2 = $test->createCallableMock();
        $mock2
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->then(
                $mock1,
                $test->expectCallableNever()
            )
            ->then(
                $test->expectCallableNever(),
                $mock2
            );
    }

    /**
     *
     */
    public function testApiCancel_ReturnsFulfilledPromise_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $test->assertInstanceOf(PromiseFulfilled::class, $deferred->getPromise()->cancel());
    }

    /**
     *
     */
    public function testApiCancel_HasNoEffect_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $deferred
            ->getPromise()
            ->cancel()
            ->then(
                null,
                null,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiDone_InvokesFulfillmentHandler_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->resolve(1);

        $test->assertNull($deferred->getPromise()->done($mock));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerThrowsInFulfillment_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');

        $deferred->resolve(1);

        $test->assertNull($deferred->getPromise()->done(function() {
            throw new Exception('UnhandledRejectionException');
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerRejectsInFulfillment_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);

        $deferred->resolve(1);

        $test->assertNull($deferred->getPromise()->done(function() {
            return Promise::doReject();
        }));
    }

    /**
     *
     */
    public function testApiFailure_DoesNotInvokeRejectionHandler_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->failure($test->expectCallableNever());
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred->resolve($value);

        $deferred
            ->getPromise()
            ->always($test->expectCallableOnce())
            ->then($mock);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue_WhenHandlerReturnsNonPromise_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred->resolve($value);

        $deferred
            ->getPromise()
            ->always(function() {
                return 1;
            })
            ->then($mock);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressValue_WhenHandlerReturnsPromise_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $value = new StdClass();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($value));

        $deferred->resolve($value);

        $deferred
            ->getPromise()
            ->always(function() {
                return Promise::doResolve(1);
            })
            ->then($mock);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerThrows_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->always(function() use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerRejects_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->resolve(1);

        $deferred
            ->getPromise()
            ->always(function() use($exception) {
                return Promise::doReject($exception);
            })
            ->then(null, $mock);
    }

    /**
     *
     */
    public function testApiIsPending_ReturnsTrue_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $test->assertFalse($deferred->getPromise()->isPending());
    }

    /**
     *
     */
    public function testApiIsFulfilled_ReturnsFalse_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $test->assertTrue($deferred->getPromise()->isFulfilled());
    }

    /**
     *
     */
    public function testApiIsRejected_ReturnsFalse_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $test->assertFalse($deferred->getPromise()->isRejected());
    }

    /**
     *
     */
    public function testApiIsCancelled_ReturnsFalse_ForFulfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->resolve();

        $test->assertFalse($deferred->getPromise()->isCancelled());
    }

    /**
     *
     */
    public function testApiSuccess_CallsThenWithValidArguments_ForFulfilledPromise()
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
    public function testApiFailure_CallsThenWithValidArguments_ForFulfilledPromise()
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
    public function testApiAbort_CallsThenWithValidArguments_ForFulfilledPromise()
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

    /**
     *
     */
    public function testApiSpread_SpreadsArguments_ForFullfilledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(1, 2, 3);

        $deferred->resolve([ 1, 2, 3 ]);
        $deferred
            ->getPromise()
            ->spread(
                $mock,
                $test->expectCallableNever(),
                $test->expectCallableNever()
            );
    }
}
