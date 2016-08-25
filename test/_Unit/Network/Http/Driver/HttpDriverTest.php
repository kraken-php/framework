<?php

namespace Kraken\_Unit\Network\Http\Driver;

use Kraken\Network\Http\Driver\HttpDriverInterface;
use Kraken\Network\Http\Driver\Reader\HttpReader;
use Kraken\Network\Http\Driver\HttpDriver;
use Kraken\Test\TUnit;
use Kraken\Util\Buffer\Buffer;
use StdClass;

class HttpDriverTest extends TUnit
{
    /**
     * @var HttpDriver
     */
    private $driver;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $driver = $this->createDriver();

        $this->assertInstanceOf(HttpDriver::class, $driver);
        $this->assertInstanceOf(HttpDriverInterface::class, $driver);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $driver = $this->createDriver();
        unset($driver);
    }

    /**
     *
     */
    public function testApiReadRequest_CallsMethodOnModel()
    {
        $driver = $this->createDriver();

        $buffer = new Buffer();
        $message = 'message';
        $result = new StdClass();

        $reader = $this->createReader();
        $reader
            ->expects($this->once())
            ->method('readRequest')
            ->with($buffer, $message)
            ->will($this->returnValue($result));

        $this->assertSame($result, $driver->readRequest($buffer, $message));
    }

    /**
     *
     */
    public function testApiReadResponse_CallsMethodOnModel()
    {
        $driver = $this->createDriver();

        $buffer = new Buffer();
        $message = 'message';
        $result = new StdClass();

        $reader = $this->createReader();
        $reader
            ->expects($this->once())
            ->method('readResponse')
            ->with($buffer, $message)
            ->will($this->returnValue($result));

        $this->assertSame($result, $driver->readResponse($buffer, $message));
    }

    /**
     * @param string[]|null $methods
     * @return HttpDriver|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createReader($methods = [])
    {
        $reader = $this->getMock(HttpReader::class, $methods, [], '', false);

        $this->setProtectedProperty($this->driver, 'reader', $reader);

        return $reader;
    }

    /**
     * @return HttpDriver
     */
    public function createDriver()
    {
        $this->driver = new HttpDriver();

        return $this->driver;
    }
}
