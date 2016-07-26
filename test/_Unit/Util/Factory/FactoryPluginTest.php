<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Mock\FactoryPluginMock;
use Kraken\_Unit\TestCase;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Util\Factory\Factory;
use Kraken\Util\Factory\FactoryInterface;
use Kraken\Util\Factory\FactoryPluginInterface;
use Exception;

class FactoryPluginTest extends TestCase
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

        $plugin = $this->createFactoryPlugin($mock);
        $factory = $this->createFactory();

        $plugin->registerPlugin($factory);
    }

    /**
     *
     */
    public function testApiRegisterPlugin_ReturnsFactoryPlugin()
    {
        $plugin = $this->createFactoryPlugin();
        $factory = $this->createFactory();

        $this->assertSame($plugin, $plugin->registerPlugin($factory));
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

        $plugin = $this->createFactoryPlugin($mock);
        $factory = $this->createFactory();

        $ex = null;
        try
        {
            $plugin->registerPlugin($factory);

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

        $plugin = $this->createFactoryPlugin(function() {}, $mock);
        $factory = $this->createFactory();

        $plugin
            ->registerPlugin($factory)
            ->unregisterPlugin($factory);
    }

    /**
     *
     */
    public function testApiUnregisterPlugin_ReturnsFactoryPlugin()
    {
        $plugin = $this->createFactoryPlugin();
        $factory = $this->createFactory();

        $this->assertSame($plugin, $plugin->unregisterPlugin($factory));
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

        $plugin = $this->createFactoryPlugin(function() {}, $mock);
        $factory = $this->createFactory();

        $plugin
            ->unregisterPlugin($factory);
    }

    /**
     * @param callable|null $register
     * @param callable|null $unregister
     * @return FactoryPluginInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createFactoryPlugin(callable $register = null, callable $unregister = null)
    {
        $register = $register === null ? function() {} : $register;
        $unregister = $unregister === null ? function() {} : $unregister;

        $mock = $this->getMock(FactoryPluginMock::class, [ 'register', 'unregister' ]);

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
     * @return FactoryInterface
     */
    public function createFactory()
    {
        return new Factory();
    }
}
