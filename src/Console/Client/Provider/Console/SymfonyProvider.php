<?php

namespace Kraken\Console\Client\Provider\Console;

use Symfony\Component\Console\Application;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class SymfonyProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Application
     */
    protected $symfony;

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $symfony = new Application();
        $symfony->setAutoExit(false);

        $this->symfony = $symfony;
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        unset($this->symfony);
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Console\Client\ConsoleCommandFactoryInterface');
        $handler = $core->make('Kraken\Console\Client\ConsoleCommandHandlerInterface');
        $console = $core->make('Kraken\Console\Client\ConsoleClientInterface');

        $cmds = (array) $factory->getDefinitions();
        $commands = [];
        foreach ($cmds as $command=>$definition)
        {
            $commands[] = $factory->create($command, [ $handler ]);
        }

        $this->symfony->addCommands($commands);

        $version = $core->version();
        $console->onCommand(function() use($version) {
            echo "KrakenPHP-v$version\n";
            $this->symfony->run();
        });
    }
}
