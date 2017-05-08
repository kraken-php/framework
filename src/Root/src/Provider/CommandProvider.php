<?php

namespace Kraken\Root\Provider;

use Kraken\Runtime\Command\CommandManager;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Runtime\Command\CommandFactory;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Util\Factory\FactoryPluginInterface;
use Exception;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Command\CommandFactoryInterface',
        'Kraken\Runtime\Command\CommandManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $factory = new CommandFactory();
        $manager = new CommandManager();

        $container->instance(
            'Kraken\Runtime\Command\CommandFactoryInterface',
            $factory
        );

        $container->instance(
            'Kraken\Runtime\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Runtime\Command\CommandFactoryInterface'
        );

        $container->remove(
            'Kraken\Runtime\Command\CommandManagerInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $factory = $container->make('Kraken\Runtime\Command\CommandFactoryInterface');

        $commands = (array) $config->get('command.models');
        foreach ($commands as $commandClass)
        {
            if (!class_exists($commandClass))
            {
                throw new ResourceUndefinedException("Command [$commandClass] does not exist.");
            }

            $factory
                ->define($commandClass, function($runtime, $context = []) use($commandClass) {
                    return new $commandClass($runtime, $context);
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
    }
}
