<?php

namespace Kraken\_Unit\Core\Provider;

use Kraken\Framework\Core\Provider\ChannelProvider;
use Kraken\Framework\Core\Provider\CommandProvider;
use Kraken\Framework\Core\Provider\ConfigProvider;
use Kraken\Framework\Core\Provider\ContainerProvider;
use Kraken\Framework\Core\Provider\CoreProvider;
use Kraken\Framework\Core\Provider\EnvironmentProvider;
use Kraken\Framework\Core\Provider\EventProvider;
use Kraken\Framework\Core\Provider\FilesystemProvider;
use Kraken\Framework\Core\Provider\LogProvider;
use Kraken\Framework\Core\Provider\LoopProvider;
use Kraken\Framework\Core\Provider\SupervisorProvider;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Core;
use Kraken\Test\TUnit;

class ProviderAllTest extends TUnit
{
    /**
     * @dataProvider providerProvider
     * @param ServiceProvider $provider
     */
    public function testApiUnregister_UnregistersAllProvidedInterfaces($provider)
    {
        $core = $this->getMock(Core::class, [], [], '', false);
        $provides = $provider->getProvides();
        $unset = [];

        $core
            ->expects($this->any())
            ->method('remove')
            ->will($this->returnCallback(function($provided) use(&$unset) {
                $unset[] = $provided;
            }));

        $provider->unregisterProvider($core);

        $this->assertSame($provides, $unset);
    }

    /**
     *
     */
    public function providerProvider()
    {
        return [
            [ new ChannelProvider() ],
            [ new CommandProvider() ],
            [ new ConfigProvider() ],
            [ new ContainerProvider() ],
            [ new CoreProvider() ],
            [ new EnvironmentProvider() ],
            [ new EventProvider() ],
            [ new FilesystemProvider() ],
            [ new LogProvider() ],
            [ new LoopProvider() ],
            [ new SupervisorProvider() ],
        ];
    }
}
