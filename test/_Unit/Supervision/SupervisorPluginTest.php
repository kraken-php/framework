<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\Supervision\Supervisor;
use Kraken\Supervision\SupervisorInterface;
use Kraken\Supervision\SupervisorPlugin;
use Kraken\Supervision\SupervisorPluginInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Exception;

class SupervisorPluginTest extends TUnit
{
    /**
     *
     */
    public function testApiRegisterPlugin_RegistersPlugin()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        $plugin = $this->createSupervisorPlugin($mock);
        $supervisor = $this->createSupervisor();

        $plugin->registerPlugin($supervisor);
    }

    /**
     *
     */
    public function testApiRegisterPlugin_ReturnsPlugin()
    {
        $plugin = $this->createSupervisorPlugin();
        $supervisor = $this->createSupervisor();

        $this->assertSame($plugin, $plugin->registerPlugin($supervisor));
    }

    /**
     *
     */
    public function testApiRegisterPlugin_ThrowsException_WhenRegisterThrows()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exp = new Exception('Error')));

        $plugin = $this->createSupervisorPlugin($mock);
        $supervisor = $this->createSupervisor();

        $ex = null;
        try
        {
            $plugin->registerPlugin($supervisor);

        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(ExecutionException::class, $ex);
        $this->assertSame($exp, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiUnregisterPlugin_UnregistersPlugin_WhenPluginIsRegistered()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        $plugin = $this->createSupervisorPlugin(function() {}, $mock);
        $supervisor = $this->createSupervisor();

        $plugin
            ->registerPlugin($supervisor)
            ->unregisterPlugin($supervisor);
    }

    /**
     *
     */
    public function testApiUnregisterPlugin_ReturnsPlugin()
    {
        $plugin = $this->createSupervisorPlugin();
        $supervisor = $this->createSupervisor();

        $this->assertSame($plugin, $plugin->unregisterPlugin($supervisor));
    }

    /**
     *
     */
    public function testApiUnregisterPlugin_DoesNothing_WhenPluginIsNotRegistered()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $plugin = $this->createSupervisorPlugin(function() {}, $mock);
        $supervisor = $this->createSupervisor();

        $plugin
            ->unregisterPlugin($supervisor);
    }

    /**
     * @param callable|null $register
     * @param callable|null $unregister
     * @return SupervisorPluginInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisorPlugin(callable $register = null, callable $unregister = null)
    {
        $register   = $register === null   ? function() {} : $register;
        $unregister = $unregister === null ? function() {} : $unregister;

        $mock = $this->getMock(SupervisorPlugin::class, [ 'register', 'unregister' ]);
        $mock
            ->expects($this->any())
            ->method('register')
            ->will($this->returnCallback($register));
        $mock
            ->expects($this->any())
            ->method('unregister')
            ->will($this->returnCallback($unregister));

        return $mock;
    }

    /**
     * @return SupervisorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor()
    {
        return $this->getMock(Supervisor::class, [], [], '', false);
    }
}
