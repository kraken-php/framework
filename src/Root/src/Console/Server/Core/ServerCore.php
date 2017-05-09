<?php

namespace Kraken\Root\Console\Server\Core;

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
            'Kraken\Root\Console\Server\Provider\ProjectProvider',
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
            'Project.Manager'   => 'Kraken\Console\Server\Manager\ProjectManagerInterface',
            'Runtime'           => 'Kraken\Runtime\RuntimeContainerInterface',
            'Runtime.Context'   => 'Kraken\Runtime\RuntimeContextInterface',
            'Supervisor'        => 'Kraken\Runtime\Supervision\SupervisorBaseInterface',
            'Supervisor.Base'   => 'Kraken\Runtime\Supervision\SupervisorBaseInterface',
            'Supervisor.Remote' => 'Kraken\Runtime\Supervision\SupervisorRemoteInterface',
            'System'            => 'Kraken\Util\System\SystemInterface'
        ];
    }
}
