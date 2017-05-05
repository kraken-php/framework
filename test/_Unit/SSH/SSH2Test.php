<?php

namespace Kraken\_Unit\SSH;

use Kraken\Loop\LoopInterface;
use Kraken\SSH\Driver\Sftp;
use Kraken\SSH\Driver\Shell;
use Kraken\SSH\SSH2;
use Kraken\SSH\SSH2AuthInterface;
use Kraken\SSH\SSH2Config;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Test\TUnit;

class SSH2Test extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $ssh2 = $this->createSSH2();
        $this->assertInstanceOf(SSH2Interface::class, $ssh2);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowThrowable()
    {
        $ssh2 = $this->createSSH2();
        unset($ssh2);
    }

    /**
     *
     */
    public function testApiConnect_DoesNothing_WhenConnectionIsNotNull()
    {
        $ssh2 = $this->createSSH2();
        $this->setProtectedProperty($ssh2, 'conn', $stream = fopen('php://memory', 'r'));

        $ssh2->on('error', $this->expectCallableNever());
        $ssh2->on('connect', $this->expectCallableNever());

        $ssh2->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsErrorEvent_WhenConnectionCouldNotBeEstablished_AndReturnedFalse()
    {
        $ssh2 = $this->createSSH2([ 'createConnection' ]);
        $ssh2
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue(false));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2, $this->isInstanceOf(ExecutionException::class));

        $ssh2->on('error', $callback);
        $ssh2->on('connect', $this->expectCallableNever());

        $ssh2->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsErrorEvent_WhenConnectionCouldNotBeEstablished_AndReturnedNonResource()
    {
        $ssh2 = $this->createSSH2([ 'createConnection' ]);
        $ssh2
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue(true));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2, $this->isInstanceOf(ExecutionException::class));

        $ssh2->on('error', $callback);
        $ssh2->on('connect', $this->expectCallableNever());

        $ssh2->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsErrorEvent_WhenAuthenticationIsInvalid()
    {
        $stream = fopen('php://memory', 'r');

        $auth = $this->getMock(SSH2AuthInterface::class, [ 'authenticate' ], [], '', false);
        $auth
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(false));

        $ssh2 = $this->createSSH2([ 'createConnection' ], [ 'auth' => $auth ]);
        $ssh2
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue($stream));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2, $this->isInstanceOf(ExecutionException::class));

        $ssh2->on('error', $callback);
        $ssh2->on('connect', $this->expectCallableNever());

        $ssh2->connect();
    }

    /**
     *
     */
    public function testApiConnect_EmitsConnectEvent()
    {
        $stream = fopen('php://memory', 'r');

        $auth = $this->getMock(SSH2AuthInterface::class, [ 'authenticate' ], [], '', false);
        $auth
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $ssh2 = $this->createSSH2([ 'createConnection' ], [ 'auth' => $auth ]);
        $ssh2
            ->expects($this->once())
            ->method('createConnection')
            ->will($this->returnValue($stream));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2);

        $ssh2->on('error', $this->expectCallableNever());
        $ssh2->on('connect', $callback);

        $ssh2->connect();
    }

    /**
     *
     */
    public function testApiDisconnect_DoesNothing_WhenConnectionIsNull()
    {
        $ssh2 = $this->createSSH2();

        $ssh2->on('disconnect', $this->expectCallableNever());

        $ssh2->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_DoesNothing_WhenConnectionIsNotResource()
    {
        $ssh2 = $this->createSSH2();
        $this->setProtectedProperty($ssh2, 'conn', true);

        $ssh2->on('disconnect', $this->expectCallableNever());

        $ssh2->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_EmitsDisconnectEvent()
    {
        $ssh2 = $this->createSSH2();
        $this->setProtectedProperty($ssh2, 'conn', $stream = fopen('php://memory', 'r'));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2);

        $ssh2->on('disconnect', $callback);

        $ssh2->disconnect();
    }

    /**
     *
     */
    public function testApiDisconnect_CallsDisconnectMethodAndRemovesListeners_OnEachDriver()
    {
        $ssh2 = $this->createSSH2();

        $stream = fopen('php://memory', 'r');

        $driver1 = $this->getMock(SSH2DriverInterface::class, [], [], '', false);
        $driver1
            ->expects($this->once())
            ->method('disconnect');
        $driver1
            ->expects($this->exactly(3))
            ->method('removeListener')
            ->with($this->isType('string'), $this->isType('callable'));

        $driver2 = $this->getMock(SSH2DriverInterface::class, [], [], '', false);
        $driver2
            ->expects($this->once())
            ->method('disconnect');
        $driver2
            ->expects($this->exactly(3))
            ->method('removeListener')
            ->with($this->isType('string'), $this->isType('callable'));

        $drivers = [ 'd1' => $driver1, 'd2' => $driver2 ];

        $this->setProtectedProperty($ssh2, 'conn', $stream);
        $this->setProtectedProperty($ssh2, 'drivers', $drivers);

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($ssh2);

        $ssh2->on('disconnect', $callback);

        $ssh2->disconnect();
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenConnectionIsNull()
    {
        $ssh2 = $this->createSSH2();

        $this->assertFalse($ssh2->isConnected());
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsFalse_WhenConnectionIsNotResource()
    {
        $ssh2 = $this->createSSH2();
        $this->setProtectedProperty($ssh2, 'conn', true);

        $this->assertFalse($ssh2->isConnected());
    }

    /**
     *
     */
    public function testApiIsConnected_ReturnsTrue_WhenConnectionIsResource()
    {
        $ssh2 = $this->createSSH2();
        $this->setProtectedProperty($ssh2, 'conn', $stream = fopen('php://memory', 'r'));

        $this->assertTrue($ssh2->isConnected());
    }

    /**
     *
     */
    public function testApiCreateDriver_ReturnsDriver_WhenDriverDoesExist()
    {
        $ssh2 = $this->createSSH2();

        $driver  = $this->getMock(SSH2DriverInterface::class, [], [], '', false);
        $drivers = [ 'name' => $driver ];

        $this->setProtectedProperty($ssh2, 'drivers', $drivers);

        $this->assertSame($driver, $ssh2->createDriver('name'));
    }

    /**
     *
     */
    public function testApiCreateDriver_ThrowsException_WhenConnectionIsNotEstablished()
    {
        $ssh2 = $this->createSSH2();

        $this->setExpectedException(ExecutionException::class);
        $ssh2->createDriver('name');
    }

    /**
     *
     */
    public function testApiCreateDriver_ThrowsException_WhenInvalidDriverIsRequested()
    {
        $ssh2 = $this->createSSH2([ 'isConnected' ]);
        $ssh2
            ->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $this->setExpectedException(InvalidArgumentException::class);
        $ssh2->createDriver('invalidDriver');
    }

    /**
     *
     */
    public function testApiCreateDriver_CreatesDriver_WhenShellIsRequested()
    {
        $ssh2 = $this->createSSH2([ 'isConnected' ]);
        $ssh2
            ->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $driver = $ssh2->createDriver(SSH2::DRIVER_SHELL);

        $this->assertInstanceOf(SSH2DriverInterface::class, $driver);
        $this->assertInstanceOf(Shell::class, $driver);
    }

    /**
     *
     */
    public function testApiCreateDriver_CreatesDriver_WhenSftpIsRequested()
    {
        $ssh2 = $this->createSSH2([ 'isConnected' ]);
        $ssh2
            ->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $driver = $ssh2->createDriver(SSH2::DRIVER_SFTP);

        $this->assertInstanceOf(SSH2DriverInterface::class, $driver);
        $this->assertInstanceOf(Sftp::class, $driver);
    }

    /**
     * Create SSH2 driver.
     *
     * @param string[]|null $methods
     * @param mixed
     * @return SSH2|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSSH2($methods = null, $constructorParams = [])
    {
        $loop = isset($constructorParams['loop'])
            ? $constructorParams['loop']
            : $this->getMock(LoopInterface::class, [], [], '', false);

        $auth = isset($constructorParams['auth'])
            ? $constructorParams['auth']
            : $this->getMock(SSH2AuthInterface::class, [], [], '', false);

        $config = new SSH2Config();

        return $this->getMock(SSH2::class, $methods, [ $auth, $config, $loop ]);
    }
}
