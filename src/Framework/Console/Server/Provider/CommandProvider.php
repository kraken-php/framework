<?php

namespace Kraken\Framework\Console\Server\Provider;

use Kraken\Runtime\Command\CommandInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\RuntimeContainerInterface;
use ReflectionClass;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $manager = $core->make('Kraken\Runtime\Command\CommandManagerInterface');

        $manager->import(
            $this->commands($runtime)
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
    protected function commands(RuntimeContainerInterface $runtime)
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
}
