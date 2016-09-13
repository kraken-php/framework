<?php

namespace Kraken\_Unit\Framework\Provider;

use Kraken\Root\Provider\ChannelProvider;
use Kraken\Root\Provider\CommandProvider;
use Kraken\Root\Provider\ConfigProvider;
use Kraken\Root\Provider\ContainerProvider;
use Kraken\Root\Provider\CoreProvider;
use Kraken\Root\Provider\EnvironmentProvider;
use Kraken\Root\Provider\EventProvider;
use Kraken\Root\Provider\FilesystemProvider;
use Kraken\Root\Provider\LogProvider;
use Kraken\Root\Provider\LoopProvider;
use Kraken\Root\Provider\SupervisorProvider;
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
