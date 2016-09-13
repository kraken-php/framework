<?php

namespace Kraken\_Unit\Framework\Console\Client\Provider;

use Kraken\Root\Console\Client\Provider\ChannelProvider;
use Kraken\Root\Console\Client\Provider\CommandProvider;
use Kraken\Root\Console\Client\Provider\ConsoleBootProvider;
use Kraken\Root\Console\Client\Provider\ConsoleProvider;
use Kraken\Console\Client\Client;
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
            [ new ConsoleBootProvider() ],
            [ new ConsoleProvider($this->getMock(Client::class, [], [], '', false)) ]
        ];
    }
}
