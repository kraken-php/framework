<?php

namespace Kraken\_Unit\Framework\Runtime\Core;

use Kraken\Core\Core;
use Kraken\Root\Runtime\Core\ProcessCore;
use Kraken\Runtime\Runtime;
use Kraken\Test\TUnit;

class ProcessCoreTest extends TUnit
{
    /**
     *
     */
    public function testCaseRuntimeUnit_IsProcess()
    {
        $core = $this->createCore();

        $this->assertSame(Runtime::UNIT_PROCESS, $core::RUNTIME_UNIT);
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
            'Kraken\Root\Provider\CommandProvider',
            'Kraken\Root\Provider\ConfigProvider',
            'Kraken\Root\Provider\ContainerProvider',
            'Kraken\Root\Provider\CoreProvider',
            'Kraken\Root\Provider\EnvironmentProvider',
            'Kraken\Root\Provider\SupervisorProvider',
            'Kraken\Root\Provider\EventProvider',
            'Kraken\Root\Provider\FilesystemProvider',
            'Kraken\Root\Provider\LogProvider',
            'Kraken\Root\Provider\LoopProvider',
            'Kraken\Root\Provider\SystemProvider',
            'Kraken\Root\Runtime\Provider\ChannelProvider',
            'Kraken\Root\Runtime\Provider\ChannelConsoleProvider',
            'Kraken\Root\Runtime\Provider\CommandProvider',
            'Kraken\Root\Runtime\Provider\SupervisorProvider',
            'Kraken\Root\Runtime\Provider\RuntimeProvider',
            'Kraken\Root\Runtime\Provider\RuntimeBootProvider',
            'Kraken\Root\Runtime\Provider\RuntimeManagerProvider'
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
            'Container'         => 'Kraken\Container\ContainerInterface',
            'Core'              => 'Kraken\Core\CoreInterface',
            'Emitter'           => 'Kraken\Event\EventEmitterInterface',
            'Environment'       => 'Kraken\Environment\EnvironmentInterface',
            'Filesystem'        => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Kraken\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Kraken\Log\LoggerInterface',
            'Loop'              => 'Dazzle\Loop\LoopInterface',
            'Runtime'           => 'Kraken\Runtime\RuntimeContainerInterface',
            'Runtime.Context'   => 'Kraken\Runtime\RuntimeContextInterface',
            'Runtime.Manager'   => 'Kraken\Runtime\RuntimeManagerInterface',
            'Supervisor'        => 'Kraken\Runtime\Supervision\SupervisorBaseInterface',
            'Supervisor.Base'   => 'Kraken\Runtime\Supervision\SupervisorBaseInterface',
            'Supervisor.Remote' => 'Kraken\Runtime\Supervision\SupervisorRemoteInterface',
            'System'            => 'Dazzle\Util\System\SystemInterface'
        ];
    }

    /**
     * @return Core
     */
    public function createCore()
    {
        return new ProcessCore();
    }
}
