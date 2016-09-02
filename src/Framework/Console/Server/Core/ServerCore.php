<?php

namespace Kraken\Framework\Console\Server\Core;

use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Runtime\Runtime;

class ServerCore extends Core implements CoreInterface
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
            'Kraken\Framework\Provider\ChannelProvider',
            'Kraken\Framework\Provider\CommandProvider',
            'Kraken\Framework\Provider\ConfigProvider',
            'Kraken\Framework\Provider\ContainerProvider',
            'Kraken\Framework\Provider\CoreProvider',
            'Kraken\Framework\Provider\EnvironmentProvider',
            'Kraken\Framework\Provider\SupervisorProvider',
            'Kraken\Framework\Provider\EventProvider',
            'Kraken\Framework\Provider\FilesystemProvider',
            'Kraken\Framework\Provider\LogProvider',
            'Kraken\Framework\Provider\LoopProvider',
            'Kraken\Framework\Console\Server\Provider\ChannelProvider',
            'Kraken\Framework\Console\Server\Provider\CommandProvider',
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
            'Loop'              => 'Kraken\Loop\LoopInterface',
            'Supervisor'        => 'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Base'   => 'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Remote' => 'Kraken\Runtime\Supervisor\SupervisorRemoteInterface'
        ];
    }
}
