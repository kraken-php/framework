<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseCancelled;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\Execution\CancellationException;
use Kraken\Test\Unit\TestCase;
use Exception;

trait PromiseCancelledPartial
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
    public function testCaseCancelledPromise_IsImmutable()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);
        $deferred->cancel(2);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testCasePromiseCancelled_InvokesNewlyAddedCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsCancellation_ToNextCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->exactly(2))
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_IgnoresReturnValue_InCancellationCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                function() {
                    return 2;
                }
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_IgnoresReturnPromisedValue_InCancellationCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                function() {
                    return Promise::doResolve();
                }
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_IgnoresReturnRejection_InCancellationCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                function() {
                    return Promise::doReject();
                }
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDone_InvokesCancellationHandler_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done(null, null, $mock));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerThrowsInCancellation_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'CancellationException');

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done(null, null, function() {
            throw new Exception('CancellationException');
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerCancelsInCancellation_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(CancellationException::class);

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done(null, null, function() {
            return Promise::doCancel();
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerCancelsWithNonException_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(CancellationException::class);

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done());
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerCancelsWithException_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'CancellationException');

        $deferred->cancel(new Exception('CancellationException'));

        $test->assertNull($deferred->getPromise()->done());
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerCancelsWithCancelledEmptyPromise_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(CancellationException::class);

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done(null, null, function() {
            return Promise::doCancel();
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerCancelsWithCancelledNonEmptyPromise_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'CancellationException');

        $deferred->cancel(1);

        $test->assertNull($deferred->getPromise()->done(null, null, function() {
            return Promise::doCancel(new Exception('CancellationException'));
        }));
    }

    /**
     *
     */
    public function testApiCancel_ReturnsCancelledPromise_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $test->assertInstanceOf(PromiseCancelled::class, $deferred->getPromise()->cancel());
    }

    /**
     *
     */
    public function testApiResolve_HasNoEffect_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $deferred
            ->getPromise()
            ->resolve()
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiReject_HasNoEffect_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $deferred
            ->getPromise()
            ->reject()
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->cancel($exception);

        $deferred
            ->getPromise()
            ->always(
                $test->expectCallableOnce()
            )
            ->then(
                null,
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerReturnsNonPromise_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->cancel($exception);

        $deferred
            ->getPromise()
            ->always(
                function() {
                    return 1;
                }
            )
            ->then(
                null,
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerReturnsPromise_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->cancel($exception);
        $deferred
            ->getPromise()
            ->always(
                function() {
                    return Promise::doResolve(1);
                }
            )
            ->then(
                null,
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerThrows_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $ex1 = new Exception();
        $ex2 = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex1));

        $deferred->cancel($ex1);

        $deferred
            ->getPromise()
            ->always(
                function() use($ex2) {
                    throw $ex2;
                }
            )
            ->then(
                null,
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerRejects_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $ex1 = new Exception();
        $ex2 = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex1));

        $deferred->cancel($ex1);

        $deferred
            ->getPromise()
            ->always(function() use($ex2) {
                return Promise::doReject($ex2);
            })
            ->then(
                null,
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiIsPending_ReturnsTrue_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $test->assertFalse($deferred->getPromise()->isPending());
    }

    /**
     *
     */
    public function testApiIsFulfilled_ReturnsFalse_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $test->assertFalse($deferred->getPromise()->isFulfilled());
    }

    /**
     *
     */
    public function testApiIsRejected_ReturnsFalse_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $test->assertFalse($deferred->getPromise()->isRejected());
    }

    /**
     *
     */
    public function testApiIsCancelled_ReturnsFalse_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->cancel();

        $test->assertTrue($deferred->getPromise()->isCancelled());
    }

    /**
     *
     */
    public function testApiSuccess_CallsThenWithValidArguments_ForCancelledPromise()
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
    public function testApiFailure_CallsThenWithValidArguments_ForCancelledPromise()
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
    public function testApiAbort_CallsThenWithValidArguments_ForCancelledPromise()
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
    public function testApiSpread_SpreadsArguments_ForCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(1, 2, 3);

        $deferred->cancel([ 1, 2, 3 ]);
        $deferred
            ->getPromise()
            ->spread(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );
    }
}
