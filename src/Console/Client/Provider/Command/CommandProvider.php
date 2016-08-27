<?php

namespace Kraken\Console\Client\Provider\Command;

use Kraken\Console\Client\Command\CommandFactory;
use Kraken\Console\Client\Command\CommandManager;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Core\CoreInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
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
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');
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

        $core->instance(
            'Kraken\Console\Client\Command\CommandFactoryInterface',
            $factory
        );

        $core->instance(
            'Kraken\Console\Client\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Console\Client\Command\CommandFactoryInterface'
        );

        $core->remove(
            'Kraken\Console\Client\Command\CommandManagerInterface'
        );
    }
}
