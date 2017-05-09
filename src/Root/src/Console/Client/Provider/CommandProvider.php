<?php

namespace Kraken\Root\Console\Client\Provider;

use Kraken\Console\Client\Command\CommandFactory;
use Kraken\Console\Client\Command\CommandManager;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Util\Factory\FactoryPluginInterface;
use Exception;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\Command\CommandFactoryInterface',
        'Kraken\Console\Client\Command\CommandManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function register(ContainerInterface $container)
    {
        $config = $container->make('Kraken\Config\ConfigInterface');
        $factory = new CommandFactory();

        $commands = (array) $config->get('command.models');
        foreach ($commands as $commandClass)
        {
            if (!class_exists($commandClass))
            {
                throw new ResourceUndefinedException("ConsoleCommand [$commandClass] does not exist.");
            }

            $factory
                ->define($commandClass, function($handler) use($commandClass) {
                    return new $commandClass($handler);
                });
        }

        $plugins = (array) $config->get('command.plugins');
        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("FactoryPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof FactoryPluginInterface))
            {
                throw new InvalidArgumentException("FactoryPlugin [$pluginClass] does not implement FactoryPluginInterface.");
            }

            $plugin->registerPlugin($factory);
        }

        $manager = new CommandManager();

        $container->instance(
            'Kraken\Console\Client\Command\CommandFactoryInterface',
            $factory
        );

        $container->instance(
            'Kraken\Console\Client\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Console\Client\Command\CommandFactoryInterface'
        );

        $container->remove(
            'Kraken\Console\Client\Command\CommandManagerInterface'
        );
    }
}
