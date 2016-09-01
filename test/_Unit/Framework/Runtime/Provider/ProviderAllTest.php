<?php

namespace Kraken\_Unit\Runtime\Provider;

use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Core;
use Kraken\Framework\Runtime\Provider\ChannelProvider;
use Kraken\Framework\Runtime\Provider\CommandProvider;
use Kraken\Framework\Runtime\Provider\ConsoleProvider;
use Kraken\Framework\Runtime\Provider\RuntimeProvider;
use Kraken\Framework\Runtime\Provider\RuntimeBootProvider;
use Kraken\Framework\Runtime\Provider\RuntimeManagerProvider;
use Kraken\Framework\Runtime\Provider\SupervisorProvider;
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
            [ new CommandProvider() ],
            [ new ConsoleProvider() ],
            [ new RuntimeProvider($this->getMock(RuntimeContainerInterface::class, [], [], '', false)) ],
            [ new RuntimeBootProvider() ],
            [ new RuntimeManagerProvider() ],
            [ new SupervisorProvider() ]
        ];
    }
}
