<?php

namespace Kraken\_Unit\Core\Provider;

use Kraken\Core\Provider\Channel\ChannelProvider;
use Kraken\Core\Provider\Command\CommandProvider;
use Kraken\Core\Provider\Config\ConfigProvider;
use Kraken\Core\Provider\Container\ContainerProvider;
use Kraken\Core\Provider\Core\CoreProvider;
use Kraken\Core\Provider\Environment\EnvironmentProvider;
use Kraken\Core\Provider\Event\EventProvider;
use Kraken\Core\Provider\Filesystem\FilesystemProvider;
use Kraken\Core\Provider\Log\LogProvider;
use Kraken\Core\Provider\Loop\LoopProvider;
use Kraken\Core\Provider\Supervisor\SupervisorProvider;
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
