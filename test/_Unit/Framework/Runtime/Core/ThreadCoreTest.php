<?php

namespace Kraken\_Unit\Framework\Runtime\Core;

use Kraken\Core\Core;
use Kraken\Framework\Runtime\Core\ThreadCore;
use Kraken\Runtime\Runtime;
use Kraken\Test\TUnit;

class ThreadCoreTest extends TUnit
{
    /**
     *
     */
    public function testCaseRuntimeUnit_IsProcess()
    {
        $core = $this->createCore();

        $this->assertSame(Runtime::UNIT_THREAD, $core::RUNTIME_UNIT);
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
            'Kraken\Framework\Core\Provider\ChannelProvider',
            'Kraken\Framework\Core\Provider\CommandProvider',
            'Kraken\Framework\Core\Provider\ConfigProvider',
            'Kraken\Framework\Core\Provider\ContainerProvider',
            'Kraken\Framework\Core\Provider\CoreProvider',
            'Kraken\Framework\Core\Provider\EnvironmentProvider',
            'Kraken\Framework\Core\Provider\SupervisorProvider',
            'Kraken\Framework\Core\Provider\EventProvider',
            'Kraken\Framework\Core\Provider\FilesystemProvider',
            'Kraken\Framework\Core\Provider\LogProvider',
            'Kraken\Framework\Core\Provider\LoopProvider',
            'Kraken\Framework\Runtime\Provider\ChannelProvider',
            'Kraken\Framework\Runtime\Provider\CommandProvider',
            'Kraken\Framework\Runtime\Provider\ConsoleProvider',
            'Kraken\Framework\Runtime\Provider\SupervisorProvider',
            'Kraken\Framework\Runtime\Provider\RuntimeManagerProvider'
        ];
    }

    /**
     * @return string[]
     */
    public function getDefaultAliases()
    {
        return [
            'Channel'           => 'Kraken\Runtime\Service\ChannelInternal',
            'Channel.Internal'  => 'Kraken\Runtime\Service\ChannelInternal',
            'Channel.Console'   => 'Kraken\Runtime\Service\ChannelConsole',
            'Command.Manager'   => 'Kraken\Runtime\Command\CommandManagerInterface',
            'Config'            => 'Kraken\Config\ConfigInterface',
            'Console'           => 'Kraken\Runtime\Service\ChannelConsole',
            'Container'         => 'Kraken\Container\ContainerInterface',
            'Core'              => 'Kraken\Core\CoreInterface',
            'Emitter'           => 'Kraken\Event\EventEmitterInterface',
            'Environment'       => 'Kraken\Environment\EnvironmentInterface',
            'Filesystem'        => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Kraken\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Kraken\Log\LoggerInterface',
            'Loop'              => 'Kraken\Loop\LoopInterface',
            'Supervisor'        => 'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Base'   => 'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Remote' => 'Kraken\Runtime\Supervisor\SupervisorRemoteInterface'
        ];
    }

    /**
     * @return Core
     */
    public function createCore()
    {
        return new ThreadCore();
    }
}
