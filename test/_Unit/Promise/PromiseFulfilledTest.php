<?php

namespace Kraken\_Unit\Promise;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\_Unit\Promise\_Bridge\DeferredBridge;
use Kraken\_Unit\Promise\_Partial\PromiseFulfilledPartial;
use Kraken\_Unit\TestCase;
use Exception;

class PromiseFulfilledTest extends TestCase
{
    use PromiseFulfilledPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $promise = null;

        return new DeferredBridge([
            'getPromise' => function() use(&$promise) {
                if (!$promise)
                {
                    throw new Exception(sprintf("[%s] must be resolved before obtaining the promise.", PromiseFulfilled::class));
                }
                return $promise;
            },
            'resolve' => function($value = null) use(&$promise) {
                if (!$promise)
                {
                    $promise = new PromiseFulfilled($value);
                }
            },
            'reject' => function() {
                throw new Exception(sprintf("You cannot call reject() for [%s].", PromiseFulfilled::class));
            },
            'cancel' => function() {
                throw new Exception(sprintf("You cannot call cancel() for [%s].", PromiseFulfilled::class));
            }
        ]);
    }

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createPromiseMock($methods = [])
    {
        return $this->getMock(PromiseFulfilled::class, $methods);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_IfConstructedWithPromise()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $promise = new PromiseFulfilled(new PromiseFulfilled());
    }
}
