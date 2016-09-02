<?php

namespace Kraken\Framework\Console\Client\Provider;

use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Core\CoreInterface;

class ConsoleBootProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $factory = $core->make('Kraken\Console\Client\Command\CommandFactoryInterface');
        $manager = $core->make('Kraken\Console\Client\Command\CommandManagerInterface');
        $channel = $core->make('Kraken\Console\Client\Service\ChannelConsole');
        $console = $core->make('Kraken\Console\Client\ClientInterface');

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
