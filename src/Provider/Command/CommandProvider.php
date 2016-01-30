<?php

namespace Kraken\Provider\Command;

use Exception;
use Kraken\Command\CommandFactory;
use Kraken\Command\CommandManager;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Exception\Resource\ResourceUndefinedException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Pattern\Factory\FactoryPluginInterface;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInputContextInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Command\CommandFactoryInterface',
        'Kraken\Command\CommandManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $context = $core->make('Kraken\Core\CoreInputContextInterface');

        $factory = new CommandFactory($context);
        $manager = new CommandManager();

        $core->instance(
            'Kraken\Command\CommandFactoryInterface',
            $factory
        );

        $core->instance(
            'Kraken\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Command\CommandFactoryInterface'
        );

        $core->remove(
            'Kraken\Command\CommandManagerInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Command\CommandFactoryInterface');

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
