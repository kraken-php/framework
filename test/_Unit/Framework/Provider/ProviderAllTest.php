<?php

namespace Kraken\_Unit\Framework\Provider;

use Kraken\Framework\Provider\ChannelProvider;
use Kraken\Framework\Provider\CommandProvider;
use Kraken\Framework\Provider\ConfigProvider;
use Kraken\Framework\Provider\ContainerProvider;
use Kraken\Framework\Provider\CoreProvider;
use Kraken\Framework\Provider\EnvironmentProvider;
use Kraken\Framework\Provider\EventProvider;
use Kraken\Framework\Provider\FilesystemProvider;
use Kraken\Framework\Provider\LogProvider;
use Kraken\Framework\Provider\LoopProvider;
use Kraken\Framework\Provider\SupervisorProvider;
use Kraken\Container\ServiceProvider;
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
