<?php

namespace Kraken\Test\Unit\Promise\_Partial;

use Kraken\Promise\Deferred;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Test\Unit\TestCase;
use Exception;

trait ApiRejectPartial
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
    public function testApiReject_RejectsPromise_WithAnImmediateValue()
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
                $mock
            );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiReject_RejectsPromise_WithFulfilledPromise()
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
                $mock
            );

        $deferred
            ->reject(Promise::doResolve(1));
    }

    /**
     *
     */
    public function testApiReject_RejectsPromise_WithRejectedPromise()
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
                $mock
            );

        $deferred
            ->reject(Promise::doReject(1));
    }

    /**
     *
     */
    public function testApiReject_ForwardsReason_WhenCallbackIsNull()
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
                $test->expectCallableNever()
            )
            ->then(
                $test->expectCallableNever(),
                $mock
            );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiReject_MakesPromiseImmutable()
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
                function($value) use($deferred) {
                    $deferred->reject(3);
                    return Promise::doReject($value);
                }
            )
            ->then(
                $test->expectCallableNever(),
                $mock
            );

        $deferred->reject(1);
        $deferred->reject(2);
    }

    /**
     *
     */
    public function testApiReject_InvokesDoneRejectionHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->with($test->identicalTo(1));

        $test->assertNull(
            $deferred->getPromise()->done(null, $mock)
        );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_FromRejectionHandler()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $test->assertNull(
            $deferred->getPromise()->done(null, function() {
                throw new \Exception('UnhandledRejectionException');
            })
        );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenRejectedWithNonException()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);
        $test->assertNull(
            $deferred->getPromise()->done()
        );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenRejectionHandlerRejects()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);
        $test->assertNull(
            $deferred->getPromise()->done(null, function() {
                return Promise::doReject();
            })
        );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenRejectionHandlerRejectsWithException()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $test->assertNull(
            $deferred->getPromise()->done(null, function() {
                return Promise::doReject(new Exception('UnhandledRejectionException'));
            })
        );

        $deferred
            ->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(RejectionException::class);
        $d = new Deferred();
        $promise = $d->getPromise();

        $test->assertNull($deferred->getPromise()->done(null, function() use($promise) {
            return $promise;
        }));

        $deferred->reject(1);
        $d->reject(1);
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_ProvidedAsRejectionValue()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $test->assertNull(
            $deferred->getPromise()->done()
        );

        $deferred
            ->reject(new Exception('UnhandledRejectionException'));
    }

    /**
     *
     */
    public function testApiDone_ThrowsException_WithDeepNestingPromiseChains()
    {
        $test = $this->getTest();

        $test->setExpectedException(Exception::class, 'UnhandledRejectionException');
        $exception = new Exception('UnhandledRejectionException');
        $d = new Deferred();

        $result = Promise::doResolve(Promise::doResolve($d->getPromise()->then(function() use($exception) {
            $d = new Deferred();
            $d->resolve();
            return Promise::doResolve($d->getPromise()->then(function() {}))->then(
                function() use($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();
        $d->resolve();
    }

    /**
     *
     */
    public function testApiDone_Recovers_WhenRejectionHandlerCatchesException()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $test->assertNull(
            $deferred->getPromise()->done(null, function($exception) {})
        );

        $deferred
            ->reject(new Exception('UnhandledRejectionException'));
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection()
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
            ->always($test->expectCallableOnce())
            ->then(
                null,
                $mock
            );

        $deferred
            ->reject($exception);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection_WhenHandlerReturnsNonPromise()
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
            ->always(function() {
                return 1;
            })
            ->then(
                null,
                $mock
            );

        $deferred
            ->reject($exception);
    }

    /**
     *
     */
    public function testApiAlways_DoesNotSuppressRejection_WhenHandlerReturnsPromise()
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
            ->always(function() {
                return Promise::doResolve(1);
            })
            ->then(
                null,
                $mock
            );

        $deferred
            ->reject($exception);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerThrows_AfterReject()
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
            ->always(function() use ($exception) {
                throw $exception;
            })
            ->then(
                null,
                $mock
            );

        $deferred
            ->reject($exception);
    }

    /**
     *
     */
    public function testApiAlways_RejectsPromise_WhenHandlerRejects_AfterReject()
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
            ->reject($exception);
    }

    /**
     *
     */
    public function testApiReject_ReturnsRejectedPromise()
    {
        $deferred = $this->createDeferred();
        $test = $this->getTest();

        $deferred
            ->getPromise()
            ->reject()
            ->then(
                null,
                $test->expectCallableOnce()
            );
    }
}
