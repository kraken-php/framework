<?php

namespace Kraken\_Unit\Console\Client\Provider;

use Kraken\Console\Client\Provider\Channel\ChannelProvider;
use Kraken\Console\Client\Provider\Command\CommandProvider;
use Kraken\Console\Client\Provider\Console\ConsoleBootProvider;
use Kraken\Console\Client\Provider\Console\ConsoleProvider;
use Kraken\Console\Client\ConsoleClient;
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
            [ new ConsoleBootProvider() ],
            [ new ConsoleProvider($this->getMock(ConsoleClient::class, [], [], '', false)) ]
        ];
    }
}
