<?php

namespace Kraken\_Module\Util\Factory;

use Kraken\_Unit\Util\Factory\_Mock\FactoryPluginMock;
use Kraken\_Unit\Util\Factory\_Mock\SimpleFactoryPluginMock;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Util\Factory\Factory;
use Kraken\Util\Factory\FactoryInterface;
use Kraken\Util\Factory\FactoryPluginInterface;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;
use Kraken\Util\Factory\SimpleFactoryPluginInterface;
use Exception;
use StdClass;

class FactoryTest extends TModule
{
    /**
     *
     */
    public function testCaseFactory_CreatesObjectUsingFactoryMethod()
    {
        $factory = $this->createFactory();

        $c1 = new StdClass;
        $c2 = new StdClass;

        $factory->bindParam('param1', $c1);
        $factory->bindParam('param2', function() use($c2) {
            return $c2;
        });

        $factory->define('Mock', function() use($factory) {
            $std = new StdClass;
            $std->param1 = $factory->getParam('param1');
            $std->param2 = $factory->getParam('param2');
            $std->args   = func_get_args();
            return $std;
        });

        $std = $factory->create('Mock', $params = [ 2, null, 'ABC' ]);

        $this->assertInstanceOf(StdClass::class, $std);
        $this->assertSame($std->param1, $c1);
        $this->assertSame($std->param2, $c2);
        $this->assertSame($std->args, $params);

        unset($factory);
    }

    /**
     *
     */
    public function testCaseSimpleFactory_CreatesObjectUsingFactoryMethod()
    {
        $factory = $this->createSimpleFactory();

        $c1 = new StdClass;
        $c2 = new StdClass;

        $factory->bindParam('param1', $c1);
        $factory->bindParam('param2', function() use($c2) {
            return $c2;
        });

        $factory->define(function() use($factory) {
            $std = new StdClass;
            $std->param1 = $factory->getParam('param1');
            $std->param2 = $factory->getParam('param2');
            $std->args   = func_get_args();
            return $std;
        });

        $std = $factory->create($params = [ 2, null, 'ABC' ]);

        $this->assertInstanceOf(StdClass::class, $std);
        $this->assertSame($std->param1, $c1);
        $this->assertSame($std->param2, $c2);
        $this->assertSame($std->args, $params);

        unset($factory);
    }

    /**
     *
     */
    public function testCaseFactoryPlugin_RegistersAndUnregistersItself()
    {
        $std = new StdClass;

        $register = function(FactoryInterface $factory) use($std) {
            $factory->define('Mock', function() use($std) {
                return $std;
            });
        };

        $unregister = function(FactoryInterface $factory) {
            $factory->remove('Mock');
        };

        $factory = $this->createFactory();
        $plugin = $this->createFactoryPlugin($register, $unregister);

        $plugin->registerPlugin($factory);
        $this->assertSame($std, $factory->create('Mock'));

        $ex = null;
        try
        {
            $plugin->unregisterPlugin($factory);
            $factory->create('Mock');
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IllegalCallException::class, $ex);

        unset($plugin);
        unset($factory);
    }

    /**
     *
     */
    public function testCaseSimpleFactoryPlugin_RegistersItself()
    {
        $std = new StdClass;

        $register = function(SimpleFactoryInterface $factory) use($std) {
            $factory->define(function() use($std) {
                return $std;
            });
        };

        $factory = $this->createSimpleFactory();
        $plugin = $this->createSimpleFactoryPlugin($register);

        $plugin->registerPlugin($factory);
        $this->assertSame($std, $factory->create());

        unset($plugin);
        unset($factory);
    }

    /**
     * @return FactoryInterface
     */
    public function createFactory()
    {
        return new Factory();
    }

    /**
     * @return SimpleFactoryInterface
     */
    public function createSimpleFactory()
    {
        return new SimpleFactory();
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
     * @param callable|null $register
     * @param callable|null $unregister
     * @return SimpleFactoryPluginInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSimpleFactoryPlugin(callable $register = null, callable $unregister = null)
    {
        $register = $register === null ? function() {} : $register;
        $unregister = $unregister === null ? function() {} : $unregister;

        $mock = $this->getMock(SimpleFactoryPluginMock::class, [ 'register', 'unregister' ]);

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
}
