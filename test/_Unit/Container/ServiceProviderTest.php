<?php

namespace Kraken\_Unit\Container;

use Kraken\Container\Container;
use Kraken\Container\ServiceProvider;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Test\TUnit;
use Exception;

class ServiceProviderTest extends TUnit
{
    /**
     *
     */
    public function testApiGetRequires_ReturnsListOfRequiredDefinitions()
    {
        $provider = $this->createProvider();
        $requires = [ 'A', 'B' ];

        $this->setProtectedProperty($provider, 'requires', $requires);
        $this->assertSame($requires, $provider->getRequires());
    }

    /**
     *
     */
    public function testApiGetProvides_ReturnsListOfProvidedDefinitions()
    {
        $provider = $this->createProvider();
        $provides = [ 'A', 'B' ];

        $this->setProtectedProperty($provider, 'provides', $provides);
        $this->assertSame($provides, $provider->getProvides());
    }

    /**
     *
     */
    public function testApiIsRegistered_ReturnsFalse_WhenProviderIsNotRegistered()
    {
        $provider = $this->createProvider();
        $registered = false;

        $this->setProtectedProperty($provider, 'registered', $registered);
        $this->assertFalse($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiIsRegistered_ReturnsTrue_WhenProviderIsRegistered()
    {
        $provider = $this->createProvider();
        $registered = true;

        $this->setProtectedProperty($provider, 'registered', $registered);
        $this->assertTrue($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiIsBooted_ReturnsFalse_WhenProviderIsNotBooted()
    {
        $provider = $this->createProvider();
        $booted = false;

        $this->setProtectedProperty($provider, 'booted', $booted);
        $this->assertFalse($provider->isBooted());
    }

    /**
     *
     */
    public function testApiIsBooted_ReturnsTrue_WhenProviderIsBooted()
    {
        $provider = $this->createProvider();
        $booted = true;

        $this->setProtectedProperty($provider, 'booted', $booted);
        $this->assertTrue($provider->isBooted());
    }


    /**
     *
     */
    public function testApiRegisterProvider_RegistersProvider()
    {
        $container = $this->getMock(Container::class, [], [], '', false);

        $provider = $this->createProvider([ 'register' ]);
        $provider
            ->expects($this->once())
            ->method('register')
            ->with($container);

        $this->assertFalse($provider->isRegistered());
        $provider->registerProvider($container);
        $this->assertTrue($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenModelThrowsException()
    {
        $container = $this->getMock(Container::class, [], [], '', false);

        $provider = $this->createProvider([ 'register' ]);
        $provider
            ->expects($this->once())
            ->method('register')
            ->with($container)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $provider->registerProvider($container);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_UnregistersProvider()
    {
        $container = $this->getMock(Container::class, [], [], '', false);

        $provider = $this->createProvider([ 'unregister' ]);
        $provider
            ->expects($this->once())
            ->method('unregister')
            ->with($container);

        $this->setProtectedProperty($provider, 'registered', true);
        $this->assertTrue($provider->isRegistered());
        $provider->unregisterProvider($container);
        $this->assertFalse($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiBootProvider_BootsProvider()
    {
        $container = $this->getMock(Container::class, [], [], '', false);

        $provider = $this->createProvider([ 'boot' ]);
        $provider
            ->expects($this->once())
            ->method('boot')
            ->with($container);

        $this->assertFalse($provider->isBooted());
        $provider->bootProvider($container);
        $this->assertTrue($provider->isBooted());
    }

    /**
     *
     */
    public function testApiBootProvider_ThrowsException_WhenModelThrowsException()
    {
        $container = $this->getMock(Container::class, [], [], '', false);

        $provider = $this->createProvider([ 'boot' ]);
        $provider
            ->expects($this->once())
            ->method('boot')
            ->with($container)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $provider->bootProvider($container);
    }


    /**
     * @param string[]|null $methods
     * @return ServiceProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProvider($methods = null)
    {
        return $this->getMock(ServiceProvider::class, $methods);
    }
}
