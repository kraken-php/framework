<?php

namespace Kraken\Test\Unit\Promise;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\PromiseInterface;
use Kraken\Promise\PromiseRejected;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Test\Unit\Promise\_Bridge\DeferredBridge;
use Kraken\Test\Unit\Promise\_Partial\PromiseRejectedPartial;
use Kraken\Test\Unit\TestCase;
use Exception;

class PromiseRejectedTest extends TestCase
{
    use PromiseRejectedPartial;

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
                    throw new Exception(sprintf("[%s] must be rejected before obtaining the promise.", PromiseRejected::class));
                }
                return $promise;
            },
            'resolve' => function() {
                throw new Exception(sprintf("You cannot call resolve() for [%s].", PromiseRejected::class));
            },
            'reject' => function($reason = null) use(&$promise) {
                if (!$promise)
                {
                    $promise = new PromiseRejected($reason);
                }
            },
            'cancel' => function() {
                throw new Exception(sprintf("You cannot call cancel() for [%s].", PromiseRejected::class));
            }
        ]);
    }

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createPromiseMock($methods = [])
    {
        return $this->getMock(PromiseRejected::class, $methods);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_IfConstructedWithPromise()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $promise = new PromiseRejected(new PromiseRejected());
    }
}
