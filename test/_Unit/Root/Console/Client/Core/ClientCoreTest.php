<?php

namespace Kraken\_Unit\Framework\Console\Server\Core;

use Kraken\Core\Core;
use Kraken\Root\Console\Client\Core\ClientCore;
use Kraken\Test\TUnit;

class ClientCoreTest extends TUnit
{
    /**
     *
     */
    public function testCaseRuntimeUnit_IsConsole()
    {
        $core = $this->createCore();

        $this->assertSame('Console', $core::RUNTIME_UNIT);
    }

    /**
     *
     */
    public function testApiGetDefaultProviders_ReturnsDefaultProviders()
    {
        $core = $this->createCore();
        $providers = $this->callProtectedMethod($core, 'getDefaultProviders');

        $this->assertSame($this->getDefaultProviders(), $providers);

        foreach ($providers as $provider)
        {
            $this->assertTrue(class_exists($provider));
        }
    }

    /**
     *
     */
    public function testApiGetDefaultAliases_ReturnsDefaultAliases()
    {
        $core = $this->createCore();
        $aliases = $this->callProtectedMethod($core, 'getDefaultAliases');

        $this->assertSame($this->getDefaultAliases(), $aliases);

        foreach ($aliases as $alias=>$target)
        {
            $this->assertTrue(interface_exists($target) || class_exists($target), "Provider $target does not exist.");
        }
    }

    /**
     * @return string[]
     */
    public function getDefaultProviders()
    {
        return [
            'Kraken\Root\Provider\ChannelProvider',
            'Kraken\Root\Provider\ConfigProvider',
            'Kraken\Root\Provider\ContainerProvider',
            'Kraken\Root\Provider\CoreProvider',
            'Kraken\Root\Provider\EnvironmentProvider',
            'Kraken\Root\Provider\EventProvider',
            'Kraken\Root\Provider\LogProvider',
            'Kraken\Root\Provider\LoopProvider',
            'Kraken\Root\Console\Client\Provider\ChannelProvider',
            'Kraken\Root\Console\Client\Provider\CommandProvider',
            'Kraken\Root\Console\Client\Provider\ConsoleProvider',
            'Kraken\Root\Console\Client\Provider\ConsoleBootProvider'
        ];
    }

    /**
     * @return string[]
     */
    public function getDefaultAliases()
    {
        return [];
    }

    /**
     * @return Core
     */
    public function createCore()
    {
        return new ClientCore();
    }
}
