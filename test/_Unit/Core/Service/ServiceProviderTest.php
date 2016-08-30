<?php

namespace Kraken\_Unit\Core\Service;

use Kraken\Core\Core;
use Kraken\Core\Service\ServiceProvider;
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
        $core = $this->getMock(Core::class, [], [], '', false);

        $provider = $this->createProvider([ 'register' ]);
        $provider
            ->expects($this->once())
            ->method('register')
            ->with($core);

        $this->assertFalse($provider->isRegistered());
        $provider->registerProvider($core);
        $this->assertTrue($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiRegisterProvider_ThrowsException_WhenModelThrowsException()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $provider = $this->createProvider([ 'register' ]);
        $provider
            ->expects($this->once())
            ->method('register')
            ->with($core)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $provider->registerProvider($core);
    }

    /**
     *
     */
    public function testApiUnregisterProvider_UnregistersProvider()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $provider = $this->createProvider([ 'unregister' ]);
        $provider
            ->expects($this->once())
            ->method('unregister')
            ->with($core);

        $this->setProtectedProperty($provider, 'registered', true);
        $this->assertTrue($provider->isRegistered());
        $provider->unregisterProvider($core);
        $this->assertFalse($provider->isRegistered());
    }

    /**
     *
     */
    public function testApiBootProvider_BootsProvider()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $provider = $this->createProvider([ 'boot' ]);
        $provider
            ->expects($this->once())
            ->method('boot')
            ->with($core);

        $this->assertFalse($provider->isBooted());
        $provider->bootProvider($core);
        $this->assertTrue($provider->isBooted());
    }

    /**
     *
     */
    public function testApiBootProvider_ThrowsException_WhenModelThrowsException()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $provider = $this->createProvider([ 'boot' ]);
        $provider
            ->expects($this->once())
            ->method('boot')
            ->with($core)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(ExecutionException::class);
        $provider->bootProvider($core);
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
