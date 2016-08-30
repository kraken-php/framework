<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;

trait FunctionCancelPartial
{
    /**
     * @see TUnit::getTest
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiDoCancel_ReturnsPromise()
    {
        $test = $this->getTest();
        $test->assertInstanceOf(PromiseInterface::class, Promise::doCancel(1));
    }
}
