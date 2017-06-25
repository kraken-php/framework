<?php

namespace Kraken\_Unit\Framework\Console\Server\Core;

use Kraken\Core\Core;
use Kraken\Root\Console\Server\Core\ServerCore;
use Kraken\Runtime\Runtime;
use Kraken\Test\TUnit;

class ServerCoreTest extends TUnit
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
    public function testApiDefaultProviders_ReturnsDefaultProviders()
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
    public function testApiDefaultAliases_ReturnsDefaultAliases()
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
            'Kraken\Root\Runtime\Provider\SupervisorProvider',
            'Kraken\Root\Runtime\Provider\RuntimeProvider',
            'Kraken\Root\Runtime\Provider\RuntimeBootProvider',
            'Kraken\Root\Runtime\Provider\RuntimeManagerProvider',
            'Kraken\Root\Console\Server\Provider\ChannelProvider',
            'Kraken\Root\Console\Server\Provider\CommandProvider',
            'Kraken\Root\Console\Server\Provider\ProjectProvider'
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
            'Command.Manager'   => 'Kraken\Runtime\Command\CommandManagerInterface',
            'Config'            => 'Kraken\Config\ConfigInterface',
            'Container'         => 'Kraken\Container\ContainerInterface',
            'Core'              => 'Kraken\Core\CoreInterface',
            'Emitter'           => 'Dazzle\Event\EventEmitterInterface',
            'Environment'       => 'Kraken\Environment\EnvironmentInterface',
            'Filesystem'        => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Kraken\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Kraken\Log\LoggerInterface',
            'Loop'              => 'Dazzle\Loop\LoopInterface',
            'Project.Manager'   => 'Kraken\Console\Server\Manager\ProjectManagerInterface',
            'Runtime'           => 'Kraken\Runtime\RuntimeContainerInterface',
            'Runtime.Context'   => 'Kraken\Runtime\RuntimeContextInterface',
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
        return new ServerCore();
    }
}
