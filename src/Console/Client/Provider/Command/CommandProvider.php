<?php

namespace Kraken\Console\Client\Provider\Command;

use Exception;
use Kraken\Console\Client\ConsoleCommandFactory;
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
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\ConsoleCommandFactoryInterface'
    ];

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');
        $factory = new ConsoleCommandFactory();

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

        $core->instance(
            'Kraken\Console\Client\ConsoleCommandFactoryInterface',
            $factory
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Console\Client\ConsoleCommandFactoryInterface'
        );
    }
}
