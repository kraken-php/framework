<?php

namespace Kraken\_Module\Core\Service;

use Kraken\_Module\Core\_Provider\AProvider;
use Kraken\_Module\Core\_Provider\BProvider;
use Kraken\_Module\Core\_Resource\Resource;
use Kraken\_Module\Core\_Resource\ResourceInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceRegister;
use Kraken\Core\Core;
use Kraken\Test\TModule;

class ServiceRegisterTest extends TModule
{
    /**
     *
     */
    public function testCaseServiceRegister_RegistersAndBootsProviders()
    {
        $core = new Core();
        $register = $this->createRegister($core);

        $register->registerProvider($b = new BProvider);
        $register->registerProvider($a = new AProvider);

        $register->boot();

        $p1 = $core->make(Resource::class);
        $p2 = $core->make(ResourceInterface::class);

        $p = $p1;

        $this->assertSame($p, $p1);
        $this->assertSame($p, $p2);
    }

    /**
     *
     */
    public function testCaseServiceRegister_RegistersAliases()
    {
        $core = new Core();
        $register = $this->createRegister($core);

        $register->registerProvider($b = new BProvider);
        $register->registerProvider($a = new AProvider);

        $register->registerAlias('A1', Resource::class);
        $register->registerAlias('A2', ResourceInterface::class);

        $register->boot();

        $p1 = $core->make(Resource::class);
        $p2 = $core->make(ResourceInterface::class);

        $a1 = $core->make(Resource::class);
        $a2 = $core->make(ResourceInterface::class);

        $p = $p1;

        $this->assertSame($p, $p1);
        $this->assertSame($p, $p2);
        $this->assertSame($p, $a1);
        $this->assertSame($p, $a2);
    }

    /**
     * @param CoreInterface $core
     * @return ServiceRegister
     */
    public function createRegister(CoreInterface $core)
    {
        return new ServiceRegister($core);
    }
}
