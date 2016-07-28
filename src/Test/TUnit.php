<?php

namespace Kraken\Test;

use Kraken\Loop\LoopInterface;
use Kraken\Test\Stub\Callback;
use ReflectionClass;

class TUnit extends \PHPUnit_Framework_TestCase
{
    /**
     * Return project root.
     *
     * @return string
     */
    public function basePath()
    {
        return realpath(__DIR__ . '/..');
    }

    /**
     * @return TUnit
     */
    public function getTest()
    {
        return $this;
    }

    /**
     * Creates a callback that must be called $amount times or the test will fail.
     *
     * @param $amount
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    public function expectCallableExactly($amount)
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
    public function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    /**
     * Creates a callback that must be called twice.
     *
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    public function expectCallableTwice()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->exactly(2))
            ->method('__invoke');

        return $mock;
    }

    /**
     * Creates a callable that must not be called once.
     *
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    public function expectCallableNever()
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
    public function createCallableMock()
    {
        return $this->getMock(Callback::class);
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLoopMock()
    {
        return $this->getMock('Kraken\Loop\LoopInterface');
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createWritableLoopMock()
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
    public function createReadableLoopMock()
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

    /**
     * Set a protected property on a given object via reflection.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @return object
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);

        return $object;
    }

    /**
     * Call protected method on a given object via reflection.
     *
     * @param object $object
     * @param string $method
     * @param mixed[] $args
     * @return mixed
     */
    public function callProtectedMethod($object, $method, $args = [])
    {
        $reflection = new ReflectionClass($object);
        $reflection_method = $reflection->getMethod($method);
        $reflection_method->setAccessible(true);

        return $reflection_method->invokeArgs($object, $args);
    }
}
