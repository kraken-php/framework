<?php

namespace Kraken\Test;

use Kraken\Test\Stub\CallableStub;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Creates a callback that must be called $amount times or the test will fail.
     *
     * @param $amount
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function expectCallableExactly($amount)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->exactly($amount))
            ->method('__invoke');

        return $mock;
    }

    /**
     * Creates a callback that must be called once.
     *
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    /**
     * Creates a callable that must not be called once.
     *
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    /**
     * Creates a callable mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCallableMock()
    {
        return $this->getMock(CallableStub::class);
    }
}
