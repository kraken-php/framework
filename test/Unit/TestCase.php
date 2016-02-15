<?php

namespace Kraken\Test\Unit;

use Kraken\Loop\LoopInterface;
use Kraken\Test\Unit\Stub\CallableStub;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Return project root.
     *
     * @return string
     */
    protected function basePath()
    {
        return realpath(__DIR__ . '/..');
    }

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
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCallableMock()
    {
        return $this->getMock(CallableStub::class);
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createLoopMock()
    {
        return $this->getMock('Kraken\Loop\LoopInterface');
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createWritableLoopMock()
    {
        $loop = $this->createLoopMock();
        $loop
            ->expects($this->once())
            ->method('addWriteStream')
            ->will($this->returnCallback(function($stream, $listener) {
                call_user_func($listener, $stream);
            }));

        return $loop;
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createReadableLoopMock()
    {
        $loop = $this->createLoopMock();
        $loop
            ->expects($this->once())
            ->method('addReadStream')
            ->will($this->returnCallback(function($stream, $listener) {
                call_user_func($listener, $stream);
            }));

        return $loop;
    }
}
