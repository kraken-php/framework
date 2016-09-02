<?php

namespace Kraken\Framework\Runtime\Core;

use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Runtime\Runtime;

class ProcessCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = Runtime::UNIT_PROCESS;

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
}
