<?php

namespace Kraken\Provider\Error;

use Exception;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Error\ErrorFactory;
use Kraken\Error\ErrorManager;
use Kraken\Throwable\Resource\ResourceUndefinedException;
use Kraken\Throwable\Runtime\InvalidArgumentException;
use Kraken\Pattern\Factory\FactoryPluginInterface;

class ErrorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Runtime\RuntimeInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Error\ErrorFactoryInterface',
        'Kraken\Error\ErrorManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');

        $factory = new ErrorFactory();
        $default = [
            'factory' => $factory
        ];

        $core->instance(
            'Kraken\Error\ErrorFactoryInterface',
            $factory
        );

        $core->factory(
            'Kraken\Error\ErrorManagerInterface',
            function($config = []) use($runtime, $default) {
                return new ErrorManager($runtime, array_merge($default, $config));
            }
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Error\ErrorFactoryInterface'
        );

        $core->remove(
            'Kraken\Error\ErrorManagerInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Error\ErrorFactoryInterface');

        $handlers = (array) $config->get('error.handlers');
        foreach ($handlers as $handlerClass)
        {
            if (!class_exists($handlerClass))
            {
                throw new ResourceUndefinedException("ErrorHandler [$handlerClass] does not exist.");
            }

            $factory
                ->define($handlerClass, function($runtime, $context = []) use($handlerClass) {
                    return new $handlerClass($runtime, $context);
                });
        }

        $plugins = (array) $config->get('error.plugins');
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
