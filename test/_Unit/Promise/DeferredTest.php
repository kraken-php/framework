<?php

namespace Kraken\_Unit\Promise;

use Kraken\_Unit\Promise\_Bridge\DeferredBridge;
use Kraken\_Unit\Promise\_Partial\FullTestPartial;
use Kraken\Promise\Deferred;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;

class DeferredTest extends TUnit
{
    use FullTestPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $d = new Deferred();

        return new DeferredBridge([
            'getPromise' => [ $d, 'getPromise' ],
            'resolve'    => [ $d, 'resolve' ],
            'reject'     => [ $d, 'reject' ],
            'cancel'     => [ $d, 'cancel' ],
        ]);
    }

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createPromiseMock($methods = [])
    {
        return $this->getMock(Promise::class, $methods);
    }
}
