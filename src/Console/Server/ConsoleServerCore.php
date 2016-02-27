<?php

namespace Kraken\Console\Server;

use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Runtime\Runtime;

class ConsoleServerCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = Runtime::UNIT_PROCESS;

    /**
     * @return string[]
     */
    protected function defaultProviders()
    {
        return [
            'Kraken\Core\Provider\Channel\ChannelProvider',
            'Kraken\Core\Provider\Command\CommandProvider',
            'Kraken\Core\Provider\Config\ConfigProvider',
            'Kraken\Core\Provider\Container\ContainerProvider',
            'Kraken\Core\Provider\Core\CoreProvider',
            'Kraken\Core\Provider\Core\EnvironmentProvider',
            'Kraken\Core\Provider\Error\ErrorProvider',
            'Kraken\Core\Provider\Event\EventProvider',
            'Kraken\Core\Provider\Filesystem\FilesystemProvider',
            'Kraken\Core\Provider\Log\LogProvider',
            'Kraken\Core\Provider\Loop\LoopProvider',
            'Kraken\Console\Server\Provider\Channel\ChannelProvider',
            'Kraken\Console\Server\Provider\Command\CommandProvider',
            'Kraken\Runtime\Provider\Command\CommandProvider',
            'Kraken\Runtime\Provider\Error\ErrorProvider',
            'Kraken\Runtime\Provider\Runtime\RuntimeManagerProvider'
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultAliases()
    {
        return [
            'Channel'           => 'Kraken\Runtime\RuntimeChannelInterface',
            'Channel.Internal'  => 'Kraken\Runtime\RuntimeChannelInterface',
            'CommandManager'    => 'Kraken\Command\CommandManagerInterface',
            'Config'            => 'Kraken\Config\ConfigInterface',
            'Container'         => 'Kraken\Container\ContainerInterface',
            'Core'              => 'Kraken\Core\CoreInterface',
            'Emitter'           => 'Kraken\Event\EventEmitterInterface',
            'Environment'       => 'Kraken\Core\EnvironmentInterface',
            'Filesystem'        => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Kraken\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Kraken\Log\LoggerInterface',
            'Loop'              => 'Kraken\Loop\LoopInterface',
            'Supervisor'        => 'Kraken\Runtime\Supervision\Base\SupervisionManagerInterface',
            'Supervisor.Base'   => 'Kraken\Runtime\Supervision\Base\SupervisionManagerInterface',
            'Supervisor.Remote' => 'Kraken\Runtime\Supervision\Remote\SupervisionManagerInterface'
        ];
    }
}
