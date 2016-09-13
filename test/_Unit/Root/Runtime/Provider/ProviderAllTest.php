<?php

namespace Kraken\_Unit\Framework\Runtime\Provider;

use Kraken\Core\Core;
use Kraken\Container\ServiceProvider;
use Kraken\Root\Runtime\Provider\ChannelProvider;
use Kraken\Root\Runtime\Provider\ChannelConsoleProvider;
use Kraken\Root\Runtime\Provider\CommandProvider;
use Kraken\Root\Runtime\Provider\RuntimeProvider;
use Kraken\Root\Runtime\Provider\RuntimeBootProvider;
use Kraken\Root\Runtime\Provider\RuntimeManagerProvider;
use Kraken\Root\Runtime\Provider\SupervisorProvider;
use Kraken\Runtime\RuntimeContainerInterface;
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
            [ new ChannelConsoleProvider() ],
            [ new CommandProvider() ],
            [ new RuntimeProvider($this->getMock(RuntimeContainerInterface::class, [], [], '', false)) ],
            [ new RuntimeBootProvider() ],
            [ new RuntimeManagerProvider() ],
            [ new SupervisorProvider() ]
        ];
    }
}
