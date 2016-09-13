<?php

namespace Kraken\Root\Provider;

use Kraken\Channel\ChannelFactory;
use Kraken\Channel\ChannelModelFactory;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Util\Factory\FactoryPluginInterface;
use Exception;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Runtime\RuntimeContextInterface',
        'Kraken\Loop\LoopInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Channel\ChannelModelFactoryInterface',
        'Kraken\Channel\ChannelModelInterface',
        'Kraken\Channel\ChannelFactoryInterface',
        'Kraken\Channel\ChannelInterface',
        'Kraken\Channel\ChannelCompositeInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $loop    = $container->make('Kraken\Loop\LoopInterface');
        $context = $container->make('Kraken\Runtime\RuntimeContextInterface');

        $modelFactory = new ChannelModelFactory($context->getAlias(), $loop);
        $factory = new ChannelFactory($context->getAlias(), $modelFactory, $loop);

        $container->instance(
            'Kraken\Channel\ChannelModelFactoryInterface',
            $modelFactory
        );

        $container->factory(
            'Kraken\Channel\ChannelModelInterface',
            function() use($modelFactory) {
                return $modelFactory->create('Kraken\Channel\Model\Null\NullModel');
            }
        );

        $container->instance(
            'Kraken\Channel\ChannelFactoryInterface',
            $factory
        );

        $container->factory(
            'Kraken\Channel\ChannelInterface',
            [ $factory, 'create' ]
        );

        $container->factory(
            'Kraken\Channel\ChannelCompositeInterface',
            [ $factory, 'create' ]
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Channel\ChannelModelFactoryInterface'
        );

        $container->remove(
            'Kraken\Channel\ChannelModelInterface'
        );

        $container->remove(
            'Kraken\Channel\ChannelFactoryInterface'
        );

        $container->remove(
            'Kraken\Channel\ChannelInterface'
        );

        $container->remove(
            'Kraken\Channel\ChannelCompositeInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $factory = $container->make('Kraken\Channel\ChannelModelFactoryInterface');

        $models = (array) $config->get('channel.models');
        foreach ($models as $modelClass)
        {
            if (!class_exists($modelClass))
            {
                throw new ResourceUndefinedException("ChannelModel [$modelClass] does not exist.");
            }

            $factory
                ->define($modelClass, function($config) use($modelClass) {
                    return new $modelClass($config);
                });
        }

        $plugins = (array) $config->get('channel.plugins');
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
