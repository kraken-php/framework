<?php

namespace Kraken\_Unit\Promise\_Partial;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\_Unit\TestCase;

trait FunctionCancelPartial
{
    /**
     * @see TestCase::getTest
     * @return TestCase
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
