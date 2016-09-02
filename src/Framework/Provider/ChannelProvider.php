<?php

namespace Kraken\Framework\Provider;

use Kraken\Channel\ChannelFactory;
use Kraken\Channel\ChannelModelFactory;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
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
        'Kraken\Core\CoreInputContextInterface',
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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $loop = $core->make('Kraken\Loop\LoopInterface');
        $context = $core->make('Kraken\Core\CoreInputContextInterface');

        $modelFactory = new ChannelModelFactory($context->getAlias(), $loop);
        $factory = new ChannelFactory($context->getAlias(), $modelFactory, $loop);

        $core->instance(
            'Kraken\Channel\ChannelModelFactoryInterface',
            $modelFactory
        );

        $core->factory(
            'Kraken\Channel\ChannelModelInterface',
            function() use($modelFactory) {
                return $modelFactory->create('Kraken\Channel\Model\Null\NullModel');
            }
        );

        $core->instance(
            'Kraken\Channel\ChannelFactoryInterface',
            $factory
        );

        $core->factory(
            'Kraken\Channel\ChannelInterface',
            [ $factory, 'create' ]
        );

        $core->factory(
            'Kraken\Channel\ChannelCompositeInterface',
            [ $factory, 'create' ]
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Channel\ChannelModelFactoryInterface'
        );

        $core->remove(
            'Kraken\Channel\ChannelModelInterface'
        );

        $core->remove(
            'Kraken\Channel\ChannelFactoryInterface'
        );

        $core->remove(
            'Kraken\Channel\ChannelInterface'
        );

        $core->remove(
            'Kraken\Channel\ChannelCompositeInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Channel\ChannelModelFactoryInterface');

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
