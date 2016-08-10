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

class CoreTest extends TModule
{
    /**
     *
     */
    public function testCaseCore_RegistersAndBootsProviders()
    {
        $core = new Core();

        $core->registerProvider($b = new BProvider);
        $core->registerProvider($a = new AProvider);

        $core->boot();

        $p1 = $core->make(Resource::class);
        $p2 = $core->make(ResourceInterface::class);

        $p = $p1;

        $this->assertSame($p, $p1);
        $this->assertSame($p, $p2);
    }

    /**
     *
     */
    public function testCaseCore_RegistersAliases()
    {
        $core = new Core();

        $core->registerProvider($b = new BProvider);
        $core->registerProvider($a = new AProvider);

        $core->registerAlias('A1', Resource::class);
        $core->registerAlias('A2', ResourceInterface::class);

        $core->boot();

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
