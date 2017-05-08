<?php

namespace Kraken\Root\Console\Client\Provider;

use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Container\ContainerInterface;

class ConsoleBootProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    protected function boot(ContainerInterface $container)
    {
        $core    = $container->make('Kraken\Core\CoreInterface');
        $factory = $container->make('Kraken\Console\Client\Command\CommandFactoryInterface');
        $manager = $container->make('Kraken\Console\Client\Command\CommandManagerInterface');
        $channel = $container->make('Kraken\Console\Client\Service\ChannelConsole');
        $console = $container->make('Kraken\Console\Client\ClientInterface');

        $cmds = (array) $factory->getDefinitions();
        $commands = [];
        foreach ($cmds as $command=>$definition)
        {
            $commands[] = $factory->create($command, [ $channel, 'Server' ]);
        }

        $manager->setAutoExit(false);
        $manager->setVersion($core->getVersion());
        $manager->addCommands($commands);

        $console->onCommand(function() use($manager) {
            $manager->run();
        });
    }
}
