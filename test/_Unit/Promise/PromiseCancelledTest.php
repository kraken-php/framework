<?php

namespace Kraken\_Unit\Promise;

use Kraken\_Unit\Promise\_Bridge\DeferredBridge;
use Kraken\_Unit\Promise\_Partial\PromiseCancelledPartial;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\PromiseCancelled;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Logic\InvalidArgumentException;
use Exception;

class PromiseCancelledTest extends TUnit
{
    use PromiseCancelledPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $promise = null;

        return new DeferredBridge([
            'getPromise' => function() use (&$promise) {
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
