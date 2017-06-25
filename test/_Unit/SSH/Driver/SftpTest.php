<?php

namespace Kraken\_Unit\SSH\Driver;

use Kraken\Loop\Loop;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Timer\TimerInterface;
use Kraken\SSH\Driver\Sftp;
use Kraken\SSH\Driver\Sftp\SftpResource;
use Kraken\SSH\SSH2;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Dazzle\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Test\TUnit;

class SftpTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $driver = $this->createDriver();
        $this->assertInstanceOf(SSH2DriverInterface::class, $driver);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowThrowable()
    {
        $driver = $this->createDriver();
        unset($driver);
    }

    /**
     *
     */
    public function testApiGetName_ReturnsSftpName()
    {
        $driver = $this->createDriver();

        $this->assertSame(SSH2::DRIVER_SFTP, $driver->getName());
    }

    /**
     *
     */
    public function testApiConnect_DoesNothing_WhenConnectionIsEstablished()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'resource', $stream = fopen('php://memory', 'r'));

        $driver->on('error', $this->expectCallableNever());
        $driver->on('connect', $this->expectCallableNever());

        $driver->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsErrorEvent_WhenConnectionCouldNotBeEstablished_AndReturnedFalse()
    {
        $driver = $this->createDriver([ 'createConnection' ]);
        $driver
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue(false));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($driver, $this->isInstanceOf(ExecutionException::class));

        $driver->on('error', $callback);
        $driver->on('connect', $this->expectCallableNever());

        $driver->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsErrorEvent_WhenConnectionCouldNotBeEstablished_AndReturnedNotResource()
    {
        $driver = $this->createDriver([ 'createConnection' ]);
        $driver
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue(true));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($driver, $this->isInstanceOf(ExecutionException::class));

        $driver->on('error', $callback);
        $driver->on('connect', $this->expectCallableNever());

        $driver->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsConnectEvent()
    {
        $stream = fopen('php://memory', 'r');

        $driver = $this->createDriver([ 'createConnection' ]);
        $driver
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue($stream));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($driver);

        $driver->on('error', $this->expectCallableNever());
        $driver->on('connect', $callback);

        $driver->connect();
    }

    /**
     *
     */
    public function testApiDisconnect_DoesNothing_WhenConnectionIsNull()
    {
        $driver = $this->createDriver();

        $driver->on('disconnect', $this->expectCallableNever());

        $driver->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_DoesNothing_WhenConnectionIsNotResource()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'resource', true);

        $driver->on('disconnect', $this->expectCallableNever());

        $driver->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_EmitsDisconnectEvent()
    {
        $driver = $this->createDriver([ 'pause', 'handleDisconnect' ]);
        $this->setProtectedProperty($driver, 'resource', $stream = fopen('php://memory', 'r+'));

        $driver
            ->expects($this->once())
            ->method('pause');
        $driver
            ->expects($this->once())
            ->method('handleDisconnect');

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($driver);

        $driver->on('disconnect', $callback);

        $driver->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_ClosesEachResource()
    {
        $driver = $this->createDriver([ 'pause', 'handleDisconnect' ]);

        $resource1 = $this->getMock(SftpResource::class, [], [], '', false);
        $resource1
            ->expects($this->once())
            ->method('close');

        $resource2 = $this->getMock(SftpResource::class, [], [], '', false);
        $resource1
            ->expects($this->once())
            ->method('close');

        $resources = [ $resource1, $resource2 ];

        $this->setProtectedProperty($driver, 'resource', $stream = fopen('php://memory', 'r+'));
        $this->setProtectedProperty($driver, 'resources', $resources);

        $driver
            ->expects($this->once())
            ->method('pause');
        $driver
            ->expects($this->once())
            ->method('handleDisconnect');

        $driver->disconnect();
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenConnectionIsNull()
    {
        $driver = $this->createDriver();

        $this->assertFalse($driver->isConnected());
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenConnectionIsNotResource()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'resource', true);

        $this->assertFalse($driver->isConnected());
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsTrue_WhenConnectionIsResource()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'resource', $stream = fopen('php://memory', 'r'));

        $this->assertTrue($driver->isConnected());
    }

    /**
     *
     */
    public function testApiResume_DoesNothing_WhenDriverIsNotPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', false);

        $driver->resume();

        $this->assertFalse($driver->isPaused());
    }

    /**
     *
     */
    public function testApiResume_ResumesDriver_WhenDriverIsPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', true);

        $driver->resume();

        $this->assertFalse($driver->isPaused());
    }

    /**
     *
     */
    public function testApiPause_PausesDriver_WhenDriverIsNotPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', false);

        $driver->pause();

        $this->assertTrue($driver->isPaused());
    }

    /**
     *
     */
    public function testApiPause_DoesNothing_WhenDriverIsPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', true);

        $driver->pause();

        $this->assertTrue($driver->isPaused());
    }

    /**
     *
     */
    public function testApiIsPaused_ReturnsFalse_WhenDriverIsNotPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', false);

        $this->assertFalse($driver->isPaused());
    }

    /**
     *
     */
    public function testApiIsPaused_ReturnsTrue_WhenDriverIsPaused()
    {
        $driver = $this->createDriver();
        $this->setProtectedProperty($driver, 'paused', true);

        $this->assertTrue($driver->isPaused());
    }

    /**
     * Create Sftp driver.
     *
     * @param string[] $methods
     * @param mixed
     * @return Sftp|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createDriver($methods = null, $constructorParams = [])
    {
        if (isset($constructorParams['ssh2']))
        {
            $ssh2 = $constructorParams['ssh2'];
        }
        else
        {
            $timer = $this->getMock(TimerInterface::class, [], [], '', false);
            $loop  = $this->getMock(Loop::class, [ 'addPeriodicTimer' ], [], '', false);
            $loop
                ->expects($this->any())
                ->method('addPeriodicTimer')
                ->will($this->returnValue($timer));

            $ssh2 = $this->getMock(SSH2Interface::class, [], [], '', false);
            $ssh2
                ->expects($this->any())
                ->method('getLoop')
                ->will($this->returnValue($loop));
        }

        $conn = isset($constructorParams['conn']) ? $constructorParams['conn'] : fopen('php://memory', 'r+');

        $mock = $this->getMock(Sftp::class, $methods, [ $ssh2, $conn ]);

        return $mock;
    }
}
