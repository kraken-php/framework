<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Runtime\Execution\CancellationException;
use Kraken\Test\Unit\TestCase;
use Exception;
use StdClass;

trait ApiCancelPartial
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
    public function testApiCancel_CancelsPromise_WithAnImmediateValue()
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
                $test->expectCallableNever(),
                $mock
            );

        $deferred
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_CancelsPromise_WithPromisedValue()
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
                $test->expectCallableNever(),
                $mock
            );

        $deferred
            ->cancel(Promise::doResolve(1));
    }

    /**
     *
     */
    public function testApiCancel_CancelsPromise_WithRejectedPromise()
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
                $test->expectCallableNever(),
                $mock
            );

        $deferred
            ->cancel(Promise::doReject(1));
    }

    /**
     *
     */
    public function testApiCancel_CancelsPromise_InRightOrder()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $str = '';

        $promise = $deferred->getPromise();
        $promise
            ->then(null, null, function() use(&$str) {
                $str .= 'A';
            })
            ->then(null, null, function() use(&$str) {
                $str .= 'B';
            });

        $promise
            ->then(null, null, function() use(&$str) {
                $str .= 'C';
            });

        $promise
            ->then(null, null, function() use(&$str) {
                $str .= 'D';
            });

        $promise->cancel();
        $test->assertEquals('ABCD', $str);
    }


    /**
     *
     */
    public function testApiCancel_ForwardsReason_WhenCallbackIsNull()
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
                $test->expectCallableNever()
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );

        $deferred
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_ForwardsReason_WithoutChange()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock1 = $test->createCallableMock();
        $mock1
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $mock2 = $test->createCallableMock();
        $mock2
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $promise1 = $deferred->getPromise();
        $promise2 = $promise1
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock1
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock2
            );

        $promise2
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_ForwardsReason_WithoutChange_WhenOnCancelThrows()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock1 = $test->createCallableMock();
        $mock1
            ->expects($test->once())
            ->method('__invoke')
            ->will($test->throwException(new Exception('onCancel has thrown exception.')));

        $mock2 = $test->createCallableMock();
        $mock2
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $promise1 = $deferred->getPromise();
        $promise2 = $promise1
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock1
            )
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock2
            );

        $promise2
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_CallsCancellerOnce_IfCalledMultipleTimes()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(null));

        $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $mock
            );

        $deferred->cancel();
        $deferred->cancel();
    }

    /**
     *
     */
    public function testApiCancel_CallsCanceller_FromDeepNestedPromiseChain()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke');

        $promise = $deferred
            ->getPromise()
            ->then(
                null,
                null,
                $mock
            )
            ->then(
                function() {
                    return (new Promise(function() {}));
                }
            )
            ->then(
                function() {
                    return (new Deferred)->getPromise();
                }
            )
            ->then(
                function() use($mock) {
                    return (new Promise(function() {}));
                }
            );

        $promise
            ->cancel();
    }

    /**
     *
     */
    public function testApiCancel_DoesNotCancelPromise_WhenOneChildCancelled()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $promise = $deferred->getPromise();

        $promise
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $test->expectCallableNever()
            );

        $child1 = $promise
            ->then();

        $child2 = $promise
            ->then();

        $child1
            ->cancel();
    }

    /**
     *
     */
    public function testApiCancel_CancelsPromise_WhenAllChildrenCancelled()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $promise = $deferred
            ->getPromise()
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $test->expectCallableOnce()
            );

        $child1 = $promise
            ->then();

        $child2 = $promise
            ->then();

        $child1->cancel();
        $child2->cancel();
    }

    /**
     *
     */
    public function testApiCancel_DoesNotCancelPromise_WhenOneChildCancelledMultipleTimes()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $promise = $deferred->getPromise();

        $promise
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $test->expectCallableNever()
            );

        $child1 = $promise
            ->then();

        $child2 = $promise
            ->then();

        $child1->cancel();
        $child1->cancel();
    }

    /**
     *
     */
    public function testApiCancel_CancelPromiseOnce_WhenCancellingMultipleTimes()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $promise = $deferred->getPromise();

        $promise
            ->then(
                $test->expectCallableNever(),
                $test->expectCallableNever(),
                $test->expectCallableOnce()
            );

        $deferred->cancel();
        $deferred->cancel();
    }

    /**
     *
     */
    public function testApiCancel_CallsCanceller_OnAllChildrenWhenCalledOnRootPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $promise = $deferred->getPromise();

        $promise
            ->then()
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        $promise
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );

        $deferred
            ->cancel();
    }

    /**
     *
     */
    public function testApiCancel_InvokesDoneCancellationHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $test->assertNull(
            $deferred->getPromise()->done(null, null, $mock)
        );

        $deferred
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_ThrowsExceptionInDone_FromCancellationHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $test->assertNull(
            $deferred->getPromise()->done(
                null,
                null,
                function() {
                    throw new Exception('UnhandledRejectionException');
                }
            )
        );

        $deferred
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiCancel_ThrowsExceptionInDone_WhenCancellationHandlerRejects()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(CancellationException::class);
        $test->assertNull(
            $deferred->getPromise()->done(
                null,
                null,
                function() {
                    return Promise::doCancel();
                }
            )
        );

        $deferred
            ->cancel(1);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation()
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
            ->always(
                $test->expectCallableOnce()
            )
            ->then(
                null,
                null,
                $mock
            );

        $deferred
            ->cancel($value);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerReturnsNonPromise()
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

        $deferred
            ->cancel($value);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressCancellation_WhenHandlerReturnsPromise()
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

        $deferred
            ->cancel($value);
    }

    /**
     *
     */
    public function testApiCancel_ReturnsCancelledPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred
            ->getPromise()
            ->cancel()
            ->then(
                null,
                null,
                $test->expectCallableOnce()
            );
    }

    /**
     *
     */
    public function testApiCancel_Returns_FromCancellationHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();
        $ex = new Exception('Error');

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo($ex));

        $deferred
            ->getPromise()
            ->spread(
                null,
                null,
                function() use($ex) {
                    return Promise::doCancel($ex);
                }
            )
            ->then(
                null,
                null,
                $mock
            );

        $deferred
            ->cancel();
    }
}
