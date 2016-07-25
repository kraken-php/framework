<?php

namespace Kraken\Test\Unit\Promise;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\PromiseCancelled;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Test\Unit\Promise\_Bridge\DeferredBridge;
use Kraken\Test\Unit\Promise\_Partial\PromiseCancelledPartial;
use Kraken\Test\Unit\TestCase;
use Exception;

class PromiseCancelledTest extends TestCase
{
    use PromiseCancelledPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $promise = null;

        return new DeferredBridge([
            'promise' => function() use (&$promise) {
                if (!$promise)
                {
                    throw new Exception(sprintf("[%s] must be cancelled before obtaining the promise.", PromiseCancelled::class));
                }
                return $promise;
            },
            'resolve' => function() {
                throw new Exception(sprintf("You cannot call resolve() for [%s].", PromiseCancelled::class));
            },
            'reject' => function() {
                throw new Exception(sprintf("You cannot call reject() for [%s].", PromiseCancelled::class));
            },
            'cancel' => function($reason = null) use(&$promise) {
                if (!$promise)
                {
                    $promise = new PromiseCancelled($reason);
                }
            }
        ]);
    }

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createPromiseMock($methods = [])
    {
        return $this->getMock(PromiseCancelled::class, $methods);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_IfConstructedWithPromise()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $promise = new PromiseCancelled(new PromiseCancelled());
    }
}
