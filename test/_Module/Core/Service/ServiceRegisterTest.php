<?php

namespace Kraken\_Module\Core\Service;

use Kraken\_Module\Core\_Provider\AProvider;
use Kraken\_Module\Core\_Provider\BProvider;
use Kraken\_Module\Core\_Resource\Resource;
use Kraken\_Module\Core\_Resource\ResourceInterface;
use Kraken\Container\Container;
use Kraken\Container\ContainerInterface;
use Kraken\Core\Service\ServiceRegister;
use Kraken\Test\TModule;

class ServiceRegisterTest extends TModule
{
    /**
     *
     */
    public function testCaseServiceRegister_RegistersAndBootsProviders()
    {
        $container = new Container();
        $register = $this->createRegister($container);

        $register->registerProvider($b = new BProvider);
        $register->registerProvider($a = new AProvider);

        $register->boot();

        $p1 = $container->make(Resource::class);
        $p2 = $container->make(ResourceInterface::class);

        $p = $p1;

        $this->assertSame($p, $p1);
        $this->assertSame($p, $p2);
    }

    /**
     *
     */
    public function testCaseServiceRegister_RegistersAliases()
    {
        $container = new Container();
        $register = $this->createRegister($container);

        $register->registerProvider($b = new BProvider);
        $register->registerProvider($a = new AProvider);

        $register->registerAlias('A1', Resource::class);
        $register->registerAlias('A2', ResourceInterface::class);

        $register->boot();

        $p1 = $container->make(Resource::class);
        $p2 = $container->make(ResourceInterface::class);

        $a1 = $container->make(Resource::class);
        $a2 = $container->make(ResourceInterface::class);

        $p = $p1;

        $this->assertSame($p, $p1);
        $this->assertSame($p, $p2);
        $this->assertSame($p, $a1);
        $this->assertSame($p, $a2);
    }

    /**
     * @param ContainerInterface $container
     * @return ServiceRegister
     */
    public function createRegister(ContainerInterface $container)
    {
        return new ServiceRegister($container);
    }
}
