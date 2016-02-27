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
            'arch:start'            => $factory->create('ArchStartCommand',         [[ 'runtime' => $runtime ]]),
            'arch:stop'             => $factory->create('ArchStopCommand',          [[ 'runtime' => $runtime ]]),
            'arch:status'           => $factory->create('ArchStatusCommand',        [[ 'runtime' => $runtime ]]),
            'process:exists'        => $factory->create('ProcessExistsCommand',     [[ 'runtime' => $runtime ]]),
            'process:create'        => $factory->create('ProcessCreateCommand',     [[ 'runtime' => $runtime ]]),
            'process:destroy'       => $factory->create('ProcessDestroyCommand',    [[ 'runtime' => $runtime ]]),
            'process:start'         => $factory->create('ProcessStartCommand',      [[ 'runtime' => $runtime ]]),
            'process:stop'          => $factory->create('ProcessStopCommand',       [[ 'runtime' => $runtime ]]),
            'processes:create'      => $factory->create('ProcessesCreateCommand',   [[ 'runtime' => $runtime ]]),
            'processes:destroy'     => $factory->create('ProcessesDestroyCommand',  [[ 'runtime' => $runtime ]]),
            'processes:start'       => $factory->create('ProcessesStartCommand',    [[ 'runtime' => $runtime ]]),
            'processes:stop'        => $factory->create('ProcessesStopCommand',     [[ 'runtime' => $runtime ]]),
            'processes:get'         => $factory->create('ProcessesGetCommand',      [[ 'runtime' => $runtime ]]),
            'thread:exists'         => $factory->create('ThreadExistsCommand',      [[ 'runtime' => $runtime ]]),
            'thread:create'         => $factory->create('ThreadCreateCommand',      [[ 'runtime' => $runtime ]]),
            'thread:destroy'        => $factory->create('ThreadDestroyCommand',     [[ 'runtime' => $runtime ]]),
            'thread:start'          => $factory->create('ThreadStartCommand',       [[ 'runtime' => $runtime ]]),
            'thread:stop'           => $factory->create('ThreadStopCommand',        [[ 'runtime' => $runtime ]]),
            'threads:create'        => $factory->create('ThreadsCreateCommand',     [[ 'runtime' => $runtime ]]),
            'threads:destroy'       => $factory->create('ThreadsDestroyCommand',    [[ 'runtime' => $runtime ]]),
            'threads:start'         => $factory->create('ThreadsStartCommand',      [[ 'runtime' => $runtime ]]),
            'threads:stop'          => $factory->create('ThreadsStopCommand',       [[ 'runtime' => $runtime ]]),
            'threads:get'           => $factory->create('ThreadsGetCommand',        [[ 'runtime' => $runtime ]]),
            'runtime:exists'        => $factory->create('RuntimeExistsCommand',     [[ 'runtime' => $runtime ]]),
            'runtime:destroy'       => $factory->create('RuntimeDestroyCommand',    [[ 'runtime' => $runtime ]]),
            'runtime:start'         => $factory->create('RuntimeStartCommand',      [[ 'runtime' => $runtime ]]),
            'runtime:stop'          => $factory->create('RuntimeStopCommand',       [[ 'runtime' => $runtime ]]),
            'runtimes:destroy'      => $factory->create('RuntimesDestroyCommand',   [[ 'runtime' => $runtime ]]),
            'runtimes:start'        => $factory->create('RuntimesStartCommand',     [[ 'runtime' => $runtime ]]),
            'runtimes:stop'         => $factory->create('RuntimesStopCommand',      [[ 'runtime' => $runtime ]]),
            'runtimes:get'          => $factory->create('RuntimesGetCommand',       [[ 'runtime' => $runtime ]]),
            'container:continue'    => $factory->create('ContainerContinueCommand', [[ 'runtime' => $runtime ]]),
            'container:destroy'     => $factory->create('ContainerDestroyCommand',  [[ 'runtime' => $runtime ]]),
            'container:start'       => $factory->create('ContainerStartCommand',    [[ 'runtime' => $runtime ]]),
            'container:stop'        => $factory->create('ContainerStopCommand',     [[ 'runtime' => $runtime ]]),
            'container:status'      => $factory->create('ContainerStatusCommand',   [[ 'runtime' => $runtime ]]),
            'cmd:ping'              => $factory->create('CmdPingCommand',           [[ 'runtime' => $runtime ]]),
            'cmd:error'             => $factory->create('CmdErrorCommand',          [[ 'runtime' => $runtime ]])
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
            $commands[$name] = $factory->create($command, [[ 'runtime' => $runtime ]]);
        }

        return $commands;
    }
}
