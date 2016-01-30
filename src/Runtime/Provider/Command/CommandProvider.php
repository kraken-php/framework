<?php

namespace Kraken\Runtime\Provider\Command;

use Kraken\Command\CommandFactoryInterface;
use Kraken\Command\CommandInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\RuntimeInterface;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Command\CommandFactoryInterface',
        'Kraken\Command\CommandManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');
        $factory = $core->make('Kraken\Command\CommandFactoryInterface');
        $manager = $core->make('Kraken\Command\CommandManagerInterface');

        $manager->import(
            $this->defaultCommands($config, $factory, $runtime)
        );

        $manager->import(
            $this->appCommands($config, $factory, $runtime)
        );
    }

    /**
     * @param ConfigInterface $config
     * @param CommandFactoryInterface $factory
     * @param RuntimeInterface $runtime
     * @return CommandInterface[]
     */
    protected function defaultCommands(ConfigInterface $config, CommandFactoryInterface $factory, RuntimeInterface $runtime)
    {
        return [
            'arch:start'            => $factory->create('ArchStartCommand', [ $runtime ]),
            'arch:stop'             => $factory->create('ArchStopCommand', [ $runtime ]),
            'arch:status'           => $factory->create('ArchStatusCommand', [ $runtime ]),
            'process:exists'        => $factory->create('ProcessExistsCommand', [ $runtime ]),
            'process:create'        => $factory->create('ProcessCreateCommand', [ $runtime ]),
            'process:destroy'       => $factory->create('ProcessDestroyCommand', [ $runtime ]),
            'process:start'         => $factory->create('ProcessStartCommand', [ $runtime ]),
            'process:stop'          => $factory->create('ProcessStopCommand', [ $runtime ]),
            'processes:create'      => $factory->create('ProcessesCreateCommand', [ $runtime ]),
            'processes:destroy'     => $factory->create('ProcessesDestroyCommand', [ $runtime ]),
            'processes:start'       => $factory->create('ProcessesStartCommand', [ $runtime ]),
            'processes:stop'        => $factory->create('ProcessesStopCommand', [ $runtime ]),
            'processes:get'         => $factory->create('ProcessesGetCommand', [ $runtime ]),
            'thread:exists'         => $factory->create('ThreadExistsCommand', [ $runtime ]),
            'thread:create'         => $factory->create('ThreadCreateCommand', [ $runtime ]),
            'thread:destroy'        => $factory->create('ThreadDestroyCommand', [ $runtime ]),
            'thread:start'          => $factory->create('ThreadStartCommand', [ $runtime ]),
            'thread:stop'           => $factory->create('ThreadStopCommand', [ $runtime ]),
            'threads:create'        => $factory->create('ThreadsCreateCommand', [ $runtime ]),
            'threads:destroy'       => $factory->create('ThreadsDestroyCommand', [ $runtime ]),
            'threads:start'         => $factory->create('ThreadsStartCommand', [ $runtime ]),
            'threads:stop'          => $factory->create('ThreadsStopCommand', [ $runtime ]),
            'threads:get'           => $factory->create('ThreadsGetCommand', [ $runtime ]),
            'runtime:exists'        => $factory->create('RuntimeExistsCommand', [ $runtime ]),
            'runtime:destroy'       => $factory->create('RuntimeDestroyCommand', [ $runtime ]),
            'runtime:start'         => $factory->create('RuntimeStartCommand', [ $runtime ]),
            'runtime:stop'          => $factory->create('RuntimeStopCommand', [ $runtime ]),
            'runtimes:destroy'      => $factory->create('RuntimesDestroyCommand', [ $runtime ]),
            'runtimes:start'        => $factory->create('RuntimesStartCommand', [ $runtime ]),
            'runtimes:stop'         => $factory->create('RuntimesStopCommand', [ $runtime ]),
            'runtimes:get'          => $factory->create('RuntimesGetCommand', [ $runtime ]),
            'container:continue'    => $factory->create('ContainerContinueCommand', [ $runtime ]),
            'container:destroy'     => $factory->create('ContainerDestroyCommand', [ $runtime ]),
            'container:start'       => $factory->create('ContainerStartCommand', [ $runtime ]),
            'container:stop'        => $factory->create('ContainerStopCommand', [ $runtime ]),
            'container:status'      => $factory->create('ContainerStatusCommand', [ $runtime ]),
            'cmd:ping'              => $factory->create('CmdPingCommand', [ $runtime ]),
            'cmd:error'             => $factory->create('CmdErrorCommand', [ $runtime ])
        ];
    }

    /**
     * @param ConfigInterface $config
     * @param CommandFactoryInterface $factory
     * @param RuntimeInterface $runtime
     * @return CommandInterface[]
     */
    protected function appCommands(ConfigInterface $config, CommandFactoryInterface $factory, RuntimeInterface $runtime)
    {
        $cmds = (array) $config->get('command.commands');
        $commands = [];
        foreach ($cmds as $name=>$command)
        {
            $commands[$name] = $factory->create($command, [ $runtime ]);
        }

        return $commands;
    }
}
