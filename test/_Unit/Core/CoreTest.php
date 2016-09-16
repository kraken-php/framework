<?php

namespace Kraken\_Unit\Core;

use Kraken\_Unit\Core\_Provider\AProvider;
use Kraken\_Unit\Core\_Provider\BProvider;
use Kraken\Container\Container;
use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceRegister;
use Kraken\Runtime\Runtime;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Test\TUnit;
use Error;
use Exception;

class CoreTest extends TUnit
{
    /**
     *
     */
    public function testCaseVersionConst_HasVersion()
    {
        $this->assertSame('0.3.0', Core::VERSION);
    }

    /**
     *
     */
    public function testCaseRuntimeUnitConst_HasUndefinedRuntimeUnit()
    {
        $this->assertSame(Runtime::UNIT_UNDEFINED, Core::RUNTIME_UNIT);
    }

    /**
     *
     */
    public function testApiConstructor_CreatesCore()
    {
        $core = $this->createCore();

        $this->assertInstanceOf(Core::class, $core);
        $this->assertInstanceOf(CoreInterface::class, $core);
        $this->assertInstanceOf(Container::class, $core);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $core = $this->createCore();
        unset($core);
    }

    /**
     *
     */
    public function testApiConfig_MergesConfig()
    {
        $core = $this->createCore();
        $this->setProtectedProperty($core, 'bootConfig', [ 'a' => 'A', 'c' => 'C' ]);
        $expected = [ 'a' => 'X', 'c' => 'C', 'b' => 'Y' ];

        $result1 = $core->config([ 'a' => 'X', 'b' => 'Y' ]);
        $result2 = $this->getProtectedProperty($core, 'bootConfig');

        $this->assertSame($expected, $result1);
        $this->assertSame($expected, $result2);
    }

    /**
     *
     */
    public function testApiGetVersion_ReturnsVersion()
    {
        $core = $this->createCore();

        $this->assertSame(Core::VERSION, $core->getVersion());
    }

    /**
     *
     */
    public function testApiGetType_ReturnsRuntimeUnit()
    {
        $core = $this->createCore();

        $this->assertSame(Core::RUNTIME_UNIT, $core->getType());
    }

    /**
     *
     */
    public function testApiGetBasePath_ReturnsBasePath()
    {
        $core = $this->createCore();

        $this->setProtectedProperty($core, 'dataPath', $path = 'basePath/dataPath');
        $result = $core->getBasePath();

        $this->assertSame('basePath', $result);
    }

    /**
     *
     */
    public function testApiGetDataPath_ReturnsDataPath()
    {
        $core = $this->createCore();

        $this->setProtectedProperty($core, 'dataPath', $path = 'dataPath');
        $result = $core->getDataPath();

        $this->assertSame($path, $result);
    }

    /**
     *
     */
    public function testApiGetDataDir_ReturnsDataDir()
    {
        $core = $this->createCore();

        $this->setProtectedProperty($core, 'dataPath', $path = 'basePath/dataPath');
        $result = $core->getDataDir();

        $this->assertSame('/dataPath', $result);
    }

    /**
     *
     */
    public function testApiBoot_BootsProviders()
    {
        $core = $this->createCore([], [ 'bootProviders' ]);
        $core
            ->expects($this->once())
            ->method('bootProviders');

        $this->assertSame($core, $core->boot());
    }

    /**
     *
     */
    public function testApiBoot_ThrowsException_WhenApiBootProvidersThrowsException()
    {
        $core = $this->createCore([], [ 'bootProviders' ]);
        $core
            ->expects($this->once())
            ->method('bootProviders')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(InstantiationException::class);
        $core->boot();
    }

    /**
     *
     */
    public function testApiRegisterProviders_RegistersProviders()
    {
        $core = $this->createCore([], [ 'registerProvider' ]);
        $core
            ->expects($this->twice())
            ->method('registerProvider');

        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();
        $providers = [ $provider1, $provider2 ];

        $core->registerProviders($providers);
    }

    /**
     *
     */
    public function testApiRegisterProvider_RegistersProvider()
    {
        $provider = $this->createProvider();
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('registerProvider')
            ->with($provider);

        $core->registerProvider($provider);
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenModelThrowsException()
    {
        $provider = $this->createProvider();
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('registerProvider')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $core->registerProvider($provider);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_UnregistersProvider()
    {
        $provider = $this->createProvider();
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('unregisterProvider')
            ->with($provider);

        $core->unregisterProvider($provider);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_ThrowsException_WhenModelThrowsException()
    {
        $provider = $this->createProvider();
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('unregisterProvider')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $core->unregisterProvider($provider);
    }

    /**
     *
     */
    public function testApiGetProvider_GetsProvider()
    {
        $provider = $this->createProvider();
        $core = $this->createCore();
        $result = $provider;

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('getProvider')
            ->with($provider)
            ->will($this->returnValue($result));

        $this->assertSame($result, $core->getProvider($provider));
    }

    /**
     *
     */
    public function testApiGetProviders_GetsProviders()
    {
        $core = $this->createCore();
        $result = [ AProvider::class, BProvider::class ];

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('getProviders')
            ->will($this->returnValue($result));

        $this->assertSame($result, $core->getProviders());
    }

    /**
     *
     */
    public function testApiGetServices_GetsServices()
    {
        $core = $this->createCore();
        $result = [ AProvider::class, BProvider::class ];

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('getServices')
            ->will($this->returnValue($result));

        $this->assertSame($result, $core->getServices());
    }

    /**
     *
     */
    public function testApiFlushProviders_FlushesProviders()
    {
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('flushProviders');

        $core->flushProviders();
    }

    /**
     *
     */
    public function testApiRegisterAliases_RegistersAliases()
    {
        $core = $this->createCore([], [ 'registerAlias' ]);
        $core
            ->expects($this->twice())
            ->method('registerAlias');

        $alias1 = 'alias1'; $target1 = 'target1';
        $alias2 = 'alias2'; $target2 = 'target2';
        $aliases = [ $alias1=>$target1, $alias2=>$target2 ];

        $core->registerAliases($aliases);
    }

    /**
     *
     */
    public function testApiRegisterAlias_RegistersAlias()
    {
        $core = $this->createCore();
        $alias = 'alias';
        $target = 'target';

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('registerAlias')
            ->with($alias, $target);

        $core->registerAlias($alias, $target);
    }

    /**
     *
     */
    public function testApiRegisterAlias_ThrowsException_WhenModelThrowsException()
    {
        $core = $this->createCore();
        $alias = 'alias';
        $target = 'target';

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('registerAlias')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $core->registerAlias($alias, $target);
    }

    /**
     *
     */
    public function testApiUnregisterAlias_UnregistersAlias()
    {
        $core = $this->createCore();
        $alias = 'alias';

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('unregisterAlias')
            ->with($alias);

        $core->unregisterAlias($alias);
    }

    /**
     *
     */
    public function testApiUnregisterAlias_ThrowsException_WhenModelThrowsException()
    {
        $core = $this->createCore();
        $alias = 'alias';

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('unregisterAlias')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $core->unregisterAlias($alias);
    }

    /**
     *
     */
    public function testApiGetAlias_ReturnsAlias()
    {
        $core = $this->createCore();
        $alias = 'alias';
        $target = 'target';

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('getAlias')
            ->with($alias)
            ->will($this->returnValue($target));

        $this->assertSame($target, $core->getAlias($alias));
    }

    /**
     *
     */
    public function testApiGetAliases_ReturnsAliases()
    {
        $core = $this->createCore();
        $alias1 = 'alias1'; $target1 = 'target1';
        $alias2 = 'alias2'; $target2 = 'target2';
        $aliases = [ $alias1=>$target1, $alias2=>$target2 ];

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('getAliases')
            ->will($this->returnValue($aliases));

        $this->assertSame($aliases, $core->getAliases());
    }

    /**
     *
     */
    public function testApiFlushAliases_FlushesAliases()
    {
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('flushAliases');

        $core->flushAliases();
    }

    /**
     *
     */
    public function testApiGetDefaultProviders_ReturnsEmptyArray()
    {
        $core = $this->createCore();

        $default = $this->callProtectedMethod($core, 'getDefaultProviders');
        $expected = [];

        $this->assertSame($expected, $default);
    }

    /**
     *
     */
    public function testApiGetDefaultAliases_ReturnsEmptyArray()
    {
        $core = $this->createCore();

        $default = $this->callProtectedMethod($core, 'getDefaultAliases');
        $expected = [];

        $this->assertSame($expected, $default);
    }

    /**
     *
     */
    public function testApiBootProviders_BootsProviders()
    {
        $core = $this->createCore();

        $register = $this->createRegister($core);
        $register
            ->expects($this->once())
            ->method('boot');

        $this->callProtectedMethod($core, 'bootProviders');
    }


    /**
     * @return ServiceProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProvider()
    {
        return $this->getMock(ServiceProvider::class, [], [], '', false);
    }

    /**
     * @param Core $core
     * @param string[] $methods
     * @return ServiceRegister|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRegister(Core $core, $methods = [])
    {
        $register = $this->getMock(ServiceRegister::class, $methods, [ $core ]);

        $this->setProtectedProperty($core, 'serviceRegister', $register);

        return $register;
    }

    /**
     * @param mixed[] $args
     * @param string[]|null $methods
     * @return Core|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createCore($args = [], $methods = null)
    {
        return $this->getMock(Core::class, $methods, $args);
    }
}
