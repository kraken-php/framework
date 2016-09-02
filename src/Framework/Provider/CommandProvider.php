<?php

namespace Kraken\Framework\Provider;

use Kraken\Runtime\Command\CommandManager;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $factory = new CommandFactory();
        $manager = new CommandManager();

        $core->instance(
            'Kraken\Runtime\Command\CommandFactoryInterface',
            $factory
        );

        $core->instance(
            'Kraken\Runtime\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Runtime\Command\CommandFactoryInterface'
        );

        $core->remove(
            'Kraken\Runtime\Command\CommandManagerInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Runtime\Command\CommandFactoryInterface');

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
