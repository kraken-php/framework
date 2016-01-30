<?php

namespace Kraken\Runtime\Process;

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
    protected function defaultProviders()
    {
        return [
            'Kraken\Provider\Channel\ChannelProvider',
            'Kraken\Provider\Command\CommandProvider',
            'Kraken\Provider\Config\ConfigProvider',
            'Kraken\Provider\Container\ContainerProvider',
            'Kraken\Provider\Core\CoreProvider',
            'Kraken\Provider\Core\EnvironmentProvider',
            'Kraken\Provider\Error\ErrorProvider',
            'Kraken\Provider\Event\EventProvider',
            'Kraken\Provider\Filesystem\FilesystemProvider',
            'Kraken\Provider\Log\LogProvider',
            'Kraken\Provider\Loop\LoopProvider',
            'Kraken\Runtime\Provider\Channel\ChannelProvider',
            'Kraken\Runtime\Provider\Command\CommandProvider',
            'Kraken\Runtime\Provider\Console\ConsoleProvider',
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
            'Channel.Console'   => 'Kraken\Runtime\RuntimeConsoleInterface',
            'CommandManager'    => 'Kraken\Command\CommandManagerInterface',
            'Config'            => 'Kraken\Config\ConfigInterface',
            'Console'           => 'Kraken\Runtime\RuntimeConsoleInterface',
            'Container'         => 'Kraken\Container\ContainerInterface',
            'Core'              => 'Kraken\Core\CoreInterface',
            'Emitter'           => 'Kraken\Event\EventEmitterInterface',
            'Environment'       => 'Kraken\Core\EnvironmentInterface',
            'ErrorManager'      => 'Kraken\Runtime\RuntimeErrorManagerInterface',
            'ErrorSupervisor'   => 'Kraken\Runtime\RuntimeErrorSupervisorInterface',
            'Filesystem'        => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Kraken\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Kraken\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Kraken\Log\LoggerInterface',
            'Loop'              => 'Kraken\Loop\LoopInterface'
        ];
    }
}
