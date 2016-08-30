<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Promise\PromiseRejected;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Test\TUnit;
use Exception;

trait PromiseRejectedPartial
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
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testCasePromiseRejected_IsImmutable()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->reject(1);
        $deferred->reject(2);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testCasePromiseRejected_InvokesNewlyAddedCallback()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiThen_ForwardsRejection()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(null);

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                function() {}
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_SwitchesFromErrbacksToCallbacks_WhenErrbackDoesNotExplicitlyPropagate()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                function($val) {
                    return $val + 1;
                }
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_SwitchesFromErrbacksToCallbacks_WhenErrbackResolves()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                function ($val) {
                    return Promise::doResolve($val + 1);
                }
            )
            ->then(
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiThen_PropagatesRejection_WhenErrbackThrows()
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

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $mock1
            )
            ->then(
                $test->expectCallableNever(),
                $mock2
            );
    }

    /**
     *
     */
    public function testApiThen_PropagatesRejection_WhenErrbackRejects()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(2));

        $deferred->reject(1);

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                function ($val) {
                    return Promise::doReject($val + 1);
                }
            )
            ->then(
                $test->expectCallableNever(),
                $mock
            );
    }

    /**
     *
     */
    public function testApiDone_InvokesRejectionHandler_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $deferred->reject(1);

        $test->assertNull($deferred->getPromise()->done(null, $mock));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerThrowsInRejection_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');

        $deferred->reject(1);

        $test->assertNull($deferred->getPromise()->done(null, function() {
            throw new Exception('UnhandledRejectionException');
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerRejectsWithNonException_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);

        $deferred->reject(1);

        $test->assertNull($deferred->getPromise()->done());
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerRejectsWithException_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'RejectionException');

        $deferred->reject(new Exception('RejectionException'));

        $test->assertNull($deferred->getPromise()->done());
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerRejectsWithRejectedEmptyPromise_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);

        $deferred->reject(1);

        $test->assertNull($deferred->getPromise()->done(null, function() {
            return Promise::doReject();
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenHandlerRejectsWithRejectedNonEmptyPromise_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');

        $deferred->reject(1);

        $test->assertNull($deferred->getPromise()->done(null, function() {
            return Promise::doReject(new Exception('UnhandledRejectionException'));
        }));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WithDeepNestingPromiseChains_ForRejectedPromise()
    {
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $exception = new Exception('UnhandledRejectionException');
        $d = new Deferred();
        $d->resolve();

        $result = Promise::doResolve(Promise::doResolve($d->getPromise()->then(function() use($exception) {
            $d = new Deferred();
            $d->resolve();
            return Promise::doResolve($d->getPromise()->then(function() {}))->then(
                function () use ($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();
    }

    /**
     *
     */
    public function testApiDone_Recovers_WhenRejectionHandlerCatchesException_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject(new Exception('UnhandledRejectionException'));

        $test->assertNull(
            $deferred->getPromise()->done(null, function($ex) {})
        );
    }

//    /**
//     *
//     */
//    public function otherwiseShouldInvokeRejectionHandlerForRejectedPromise()
//    {
//        $deferred = $this->createDeferred();
//        $mock = $test->createCallableMock();
//        $mock
//            ->expects($test->once())
//            ->method('__invoke')
//            ->with($test->identicalTo(1));
//        $deferred->reject(1);
//        $deferred->getPromise()->otherwise($mock);
//    }

//    /**
//     *
//     */
//    public function otherwiseShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise()
//    {
//        $deferred = $this->createDeferred();
//        $exception = new \Exception();
//        $mock = $test->createCallableMock();
//        $mock
//            ->expects($test->once())
//            ->method('__invoke')
//            ->with($test->identicalTo($exception));
//        $deferred->reject($exception);
//        $deferred->getPromise()
//            ->otherwise(function ($reason) use ($mock) {
//                $mock($reason);
//            });
//    }

//    /**
//     *
//     */
//    public function otherwiseShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise()
//    {
//        $deferred = $this->createDeferred();
//        $exception = new \InvalidArgumentException();
//        $mock = $test->createCallableMock();
//        $mock
//            ->expects($test->once())
//            ->method('__invoke')
//            ->with($test->identicalTo($exception));
//        $deferred->reject($exception);
//        $deferred->getPromise()
//            ->otherwise(function (\InvalidArgumentException $reason) use ($mock) {
//                $mock($reason);
//            });
//    }

//    /**
//     *
//     */
//    public function otherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise()
//    {
//        $deferred = $this->createDeferred();
//        $exception = new \Exception();
//        $mock = $test->expectCallableNever();
//        $deferred->reject($exception);
//        $deferred->getPromise()
//            ->otherwise(function (\InvalidArgumentException $reason) use ($mock) {
//                $mock($reason);
//            });
//    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->reject($exception);

        $deferred
            ->getPromise()
            ->always($test->expectCallableOnce())
            ->then(
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection_WhenHandlerReturnsNonPromise_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->reject($exception);

        $deferred
            ->getPromise()
            ->always(function() {
                return 1;
            })
            ->then(null, $mock);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection_WhenHandlerReturnsPromise_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $exception = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($exception));

        $deferred->reject($exception);
        $deferred
            ->getPromise()
            ->always(function() {
                return Promise::doResolve(1);
            })
            ->then(
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerThrows_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $ex1 = new Exception();
        $ex2 = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex2));

        $deferred->reject($ex1);

        $deferred
            ->getPromise()
            ->always(function() use($ex2) {
                throw $ex2;
            })
            ->then(
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerRejects_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $ex1 = new Exception();
        $ex2 = new Exception();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex2));

        $deferred->reject($ex1);

        $deferred
            ->getPromise()
            ->always(function() use ($ex2) {
                return Promise::doReject($ex2);
            })
            ->then(
                null,
                $mock
            );
    }

    /**
     *
     */
    public function testApiCancel_ReturnsRejectedPromise_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

        $test->assertInstanceOf(PromiseRejected::class, $deferred->getPromise()->cancel());
    }

    /**
     *
     */
    public function testApiCancel_HasNoEffect_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

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
    public function testApiIsPending_ReturnsTrue_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

        $test->assertFalse($deferred->getPromise()->isPending());
    }

    /**
     *
     */
    public function testApiIsFulfilled_ReturnsFalse_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

        $test->assertFalse($deferred->getPromise()->isFulfilled());
    }

    /**
     *
     */
    public function testApiIsRejected_ReturnsFalse_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

        $test->assertTrue($deferred->getPromise()->isRejected());
    }

    /**
     *
     */
    public function testApiIsCancelled_ReturnsFalse_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred->reject();

        $test->assertFalse($deferred->getPromise()->isCancelled());
    }

    /**
     *
     */
    public function testApiSuccess_CallsThenWithValidArguments_ForRejectedPromise()
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
    public function testApiFailure_CallsThenWithValidArguments_ForRejectedPromise()
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
    public function testApiAbort_CallsThenWithValidArguments_ForRejectedPromise()
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
    public function testApiSpread_SpreadsArguments_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with(1, 2, 3);

        $deferred->reject([ 1, 2, 3 ]);
        $deferred
            ->getPromise()
            ->spread(
                $test->expectCallableNever(),
                $mock,
                $test->expectCallableNever()
            );
    }

    /**
     *
     */
    public function testApiSpread_Returns_FromRejectionHandler_ForRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $ex = new Exception('Error');

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex));

        $deferred->reject();
        $deferred
            ->getPromise()
            ->spread(
                null,
                function() use($ex) {
                    return Promise::doReject($ex);
                }
            )
            ->then(
                null,
                $mock
            );
    }
}
