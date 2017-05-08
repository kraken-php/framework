<?php

namespace Kraken\Root\Console\Server\Provider;

use Kraken\Config\ConfigInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Runtime\Command\CommandFactoryInterface;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Runtime\RuntimeContainerInterface;
use ReflectionClass;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $core
     */
    protected function boot(ContainerInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $factory = $core->make('Kraken\Runtime\Command\CommandFactoryInterface');
        $manager = $core->make('Kraken\Runtime\Command\CommandManagerInterface');

        $manager->import(
            $this->getDefaultCommands($runtime)
        );

        $manager->import(
            $this->getAppCommands($config, $factory, $runtime)
        );
    }

    /**
     * @param string $class
     * @param mixed[] $params
     * @return CommandInterface
     */
    protected function create($class, $params = [])
    {
        return (new ReflectionClass($class))->newInstanceArgs($params);
    }

    /**
     * @param RuntimeContainerInterface $runtime
     * @return CommandInterface[]
     */
    protected function getDefaultCommands(RuntimeContainerInterface $runtime)
    {
        $cmds = [
            'project:create'    => 'Kraken\Console\Server\Command\Project\ProjectCreateCommand',
            'project:destroy'   => 'Kraken\Console\Server\Command\Project\ProjectDestroyCommand',
            'project:start'     => 'Kraken\Console\Server\Command\Project\ProjectStartCommand',
            'project:stop'      => 'Kraken\Console\Server\Command\Project\ProjectStopCommand',
            'project:status'    => 'Kraken\Console\Server\Command\Project\ProjectStatusCommand',
            'server:ping'       => 'Kraken\Console\Server\Command\Server\ServerPingCommand'
        ];

        foreach ($cmds as $key=>$class)
        {
            $cmds[$key] = $this->create($class, [[ 'runtime' => $runtime ]]);
        }

        return $cmds;
    }

    /**
     * @param ConfigInterface $config
     * @param CommandFactoryInterface $factory
     * @param RuntimeContainerInterface $runtime
     * @return CommandInterface[]
     */
    protected function getAppCommands(ConfigInterface $config, CommandFactoryInterface $factory, RuntimeContainerInterface $runtime)
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
