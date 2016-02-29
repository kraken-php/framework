<?php

namespace Kraken\Console\Server\Provider\Command;

use Kraken\Command\CommandInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\RuntimeInterface;
use ReflectionClass;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');
        $manager = $core->make('Kraken\Command\CommandManagerInterface');

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
     * @param RuntimeInterface $runtime
     * @return CommandInterface[]
     */
    protected function commands(RuntimeInterface $runtime)
    {
        $cmds = [
            'project:create'    => 'Kraken\Console\Server\Command\Project\ProjectCreateCommand',
            'project:destroy'   => 'Kraken\Console\Server\Command\Project\ProjectDestroyCommand',
            'project:start'     => 'Kraken\Console\Server\Command\Project\ProjectStartCommand',
            'project:stop'      => 'Kraken\Console\Server\Command\Project\ProjectStopCommand',
            'project:status'    => 'Kraken\Console\Server\Command\Project\ProjectStatusCommand',
        ];

        foreach ($cmds as $key=>$class)
        {
            $cmds[$key] = $this->create($class, [[ 'runtime' => $runtime ]]);
        }

        return $cmds;
    }
}
