<?php

namespace Kraken\_Unit\Container;

use Kraken\_Unit\Container\_Provider\AProvider;
use Kraken\_Unit\Container\_Provider\BProvider;
use Kraken\_Unit\Container\_Provider\CProvider;
use Kraken\_Unit\Container\_Provider\DProvider;
use Kraken\_Unit\Container\_Provider\EProvider;
use Kraken\_Unit\Container\_Provider\NonProvider;
use Kraken\Container\ServiceRegister;
use Kraken\Container\Container;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Test\TUnit;
use Exception;

class ServiceRegisterTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $register = $this->createRegister();

        $this->assertInstanceOf(ServiceRegister::class, $register);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $register = $this->createRegister();
        unset($register);
    }

    /**
     *
     */
    public function testApiRegisterProvider_RegistersProvider()
    {
        $register = $this->createRegister();
        $provider = $this->getMock(AProvider::class);
        $provider
            ->expects($this->never())
            ->method('registerProvider');

        $register->registerProvider($provider);

        $this->assertSame(
            [ $provider ],
            $this->getProtectedProperty($register, 'serviceProviders')
        );
    }

    /**
     *
     */
    public function testApiRegisterProvider_ResolvesClassOfProvider()
    {
        $register = $this->createRegister();
        $provider = AProvider::class;

        $register->registerProvider($provider);

        $providers = $this->getProtectedProperty($register, 'serviceProviders');

        $this->assertInstanceOf($provider, $providers[0]);
    }

    /**
     *
     */
    public function testApiRegisterProvider_RegistersProviderImmediately_WhenRegisterIsAlreadyBooted()
    {
        $register = $this->createRegister();
        $provider = $this->getMock(AProvider::class);
        $provider
            ->expects($this->once())
            ->method('registerProvider');

        $this->setProtectedProperty($register, 'booted', true);
        $register->registerProvider($provider);

        $this->assertSame(
            [ $provider ],
            $this->getProtectedProperty($register, 'serviceProviders')
        );
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenSameProviderIsAlreadyRegistered()
    {
        $register = $this->createRegister();
        $provider = $this->getMock(AProvider::class);

        $register->registerProvider($provider);
        $this->setExpectedException(ResourceOccupiedException::class);
        $register->registerProvider($provider);
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenInvalidClassPassed()
    {
        $register = $this->createRegister();
        $provider = NonProvider::class;

        $this->setExpectedException(InvalidArgumentException::class);
        $register->registerProvider($provider);
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenProviderMethodThrowsException()
    {
        $register = $this->createRegister();
        $this->setProtectedProperty($register, 'booted', true);

        $provider = $this->getMock(AProvider::class);
        $provider
            ->expects($this->once())
            ->method('registerProvider')
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $register->registerProvider($provider);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_UnregistersProvider()
    {
        $register = $this->createRegister();
        $provider = $this->getMock(AProvider::class);
        $provider
            ->expects($this->once())
            ->method('unregisterProvider');

        $register->registerProvider($provider);
        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([ $provider ], $providers);

        $register->unregisterProvider($provider);
        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([], $providers);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_ResolvesClassOfProvider()
    {
        $register = $this->createRegister();
        $concrete = new AProvider;
        $provider = AProvider::class;

        $register->registerProvider($concrete);
        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([ $concrete ], $providers);

        $register->unregisterProvider($provider);
        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([], $providers);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_ThrowsException_WhenProviderIsNotRegistered()
    {
        $register = $this->createRegister();
        $provider = new AProvider;

        $this->setExpectedException(ResourceUndefinedException::class);
        $register->unregisterProvider($provider);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_ThrowsException_WhenInvalidClassPassed()
    {
        $register = $this->createRegister();
        $provider = NonProvider::class;

        $this->setExpectedException(InvalidArgumentException::class);
        $register->unregisterProvider($provider);
    }

    /**
     *
     */
    public function testApiGetProvider_ReturnsProvider_WhenProviderDoesExistForConcrete()
    {
        $register = $this->createRegister();
        $provider = new AProvider;

        $register->registerProvider($provider);
        $result = $register->getProvider($provider);

        $this->assertSame($provider, $result);
    }

    /**
     *
     */
    public function testApiGetProvider_ReturnsProvider_WhenProviderDoesExistForClass()
    {
        $register = $this->createRegister();
        $provider = new AProvider;

        $register->registerProvider($provider);
        $result = $register->getProvider(AProvider::class);

        $this->assertSame($provider, $result);
    }

    /**
     *
     */
    public function testApiGetProvider_ReturnsNull_WhenProviderDoesNotExist()
    {
        $register = $this->createRegister();

        $result = $register->getProvider(AProvider::class);

        $this->assertSame(null, $result);
    }

    /**
     *
     */
    public function testApiGetProviders_ReturnsListOfProviders()
    {
        $register = $this->createRegister();
        $providers = [
            AProvider::class,
            BProvider::class,
            CProvider::class,
            DProvider::class,
            EProvider::class
        ];

        foreach ($providers as $provider)
        {
            $register->registerProvider($provider);
        }

        $this->assertSame($providers, $register->getProviders());
    }

    /**
     *
     */
    public function testApiGetServices_ReturnsListOfServices()
    {
        $register = $this->createRegister();
        $providers = [
            AProvider::class,
            BProvider::class,
            CProvider::class,
            DProvider::class,
            EProvider::class
        ];
        $services = [];

        foreach ($providers as $class)
        {
            $provider = new $class;
            $register->registerProvider($provider);
            $services = array_merge($services, $provider->getProvides());
        }

        $this->assertSame($services, $register->getServices());
    }

    /**
     *
     */
    public function testApiFlushProviders_FlushProviders_BeforeBootUp()
    {
        $register = $this->createRegister();

        $register->registerProvider($a = new AProvider);
        $register->registerProvider($b = new BProvider);
        $register->registerProvider($c = new CProvider);

        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([ $a, $b, $c ], $providers);

        $register->flushProviders();

        $providers = $this->getProtectedProperty($register, 'serviceProviders');
        $this->assertSame([], $providers);
    }

    /**
     *
     */
    public function testApiFlushProviders_ThrowsException_AfterBootUp()
    {
        $register = $this->createRegister();
        $this->setProtectedProperty($register, 'booted', true);

        $this->setExpectedException(IllegalCallException::class);
        $register->flushProviders();
    }

    /**
     *
     */
    public function testApiRegisterAlias_RegistersAlias()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $aliases = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame([], $aliases);

        $register->registerAlias($alias, $existing);

        $aliases = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame([ $alias=>$existing ], $aliases);
    }

    /**
     *
     */
    public function testApiRegisterAlias_RegistersAliasImmediately_WhenRegisterIsBooted()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $container = $this->getMock(Container::class);
        $container
            ->expects($this->once())
            ->method('alias')
            ->with($alias, $existing);

        $this->setProtectedProperty($register, 'container', $container);
        $this->setProtectedProperty($register, 'booted', true);
        $register->registerAlias($alias, $existing);
    }

    /**
     *
     */
    public function testApiRegisterAlias_ThrowsException_WhenAliasDoesExist()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $register->registerAlias($alias, $existing);
        $this->setExpectedException(ResourceOccupiedException::class);
        $register->registerAlias($alias, $existing);
    }

    /**
     *
     */
    public function testApiRegisterAlias_ThrowsException_WhenModelThrowsException()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $container = $this->getMock(Container::class);
        $container
            ->expects($this->once())
            ->method('alias')
            ->with($alias, $existing)
            ->will($this->throwException(new Exception));

        $this->setProtectedProperty($register, 'container', $container);
        $this->setProtectedProperty($register, 'booted', true);

        $this->setExpectedException(ExecutionException::class);
        $register->registerAlias($alias, $existing);
    }

    /**
     *
     */
    public function testApiUnregisterAlias_UnregistersAlias()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $register->registerAlias($alias, $existing);
        $aliases = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame([ $alias=>$existing ], $aliases);

        $register->unregisterAlias($alias);
        $aliases = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame([], $aliases);
    }

    /**
     *
     */
    public function testApiUnregisterAlias_ThrowsException_WhenAliasDoesNotExist()
    {
        $register = $this->createRegister();
        $alias = 'alias';

        $this->setExpectedException(ResourceUndefinedException::class);
        $register->unregisterAlias($alias);
    }

    /**
     *
     */
    public function testApiGetAlias_ReturnsAliasPointer_WhenAliasDoesExist()
    {
        $register = $this->createRegister();
        $alias = 'alias';
        $existing = 'existing';

        $register->registerAlias($alias, $existing);
        $result = $register->getAlias($alias);

        $this->assertSame($existing, $result);
    }

    /**
     *
     */
    public function testApiGetAlias_ReturnsNull_WhenAliasDoesNotExist()
    {
        $register = $this->createRegister();
        $alias = 'alias';

        $result = $register->getAlias($alias);

        $this->assertSame(null, $result);
    }

    /**
     *
     */
    public function testApiGetAliases_ReturnsListOfAliases()
    {
        $register = $this->createRegister();
        $aliases = [
            'alias1' => 'existing1',
            'alias2' => 'existing2',
            'alias3' => 'existing3',
        ];

        foreach ($aliases as $alias=>$target)
        {
            $register->registerAlias($alias, $target);
        }

        $this->assertSame($aliases, $register->getAliases());
    }

    /**
     *
     */
    public function testApiFlushAliases_FlushAliases_BeforeBootUp()
    {
        $register = $this->createRegister();
        $aliases = [
            'alias1' => 'existing1',
            'alias2' => 'existing2',
            'alias3' => 'existing3',
        ];

        foreach ($aliases as $alias=>$target)
        {
            $register->registerAlias($alias, $target);
        }

        $providers = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame($aliases, $providers);

        $register->flushAliases();

        $providers = $this->getProtectedProperty($register, 'serviceAliases');
        $this->assertSame([], $providers);
    }

    /**
     *
     */
    public function testApiFlushAliases_ThrowsException_AfterBootUp()
    {
        $register = $this->createRegister();
        $this->setProtectedProperty($register, 'booted', true);

        $this->setExpectedException(IllegalCallException::class);
        $register->flushProviders();
    }

    /**
     * @param string[]|null $methods
     * @return ServiceRegister|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRegister($methods = null)
    {
        $container = $this->getMock(Container::class);

        return $this->getMock(ServiceRegister::class, $methods, [ $container ]);
    }
}
