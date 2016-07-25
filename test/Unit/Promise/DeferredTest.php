<?php

namespace Kraken\Test\Unit\Promise;

use Kraken\Promise\Deferred;
use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\Promise\_Bridge\DeferredBridge;
use Kraken\Test\Unit\Promise\_Partial\FullTestPartial;
use Kraken\Test\Unit\TestCase;

class DeferredTest extends TestCase
{
    use FullTestPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $d = new Deferred();

        return new DeferredBridge([
            'promise' => [ $d, 'promise' ],
            'resolve' => [ $d, 'resolve' ],
            'reject'  => [ $d, 'reject' ],
            'cancel'  => [ $d, 'cancel' ],
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
