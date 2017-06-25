<?php

namespace Kraken\Root\Provider;

use Dazzle\Channel\Model\ModelFactory;
use Dazzle\Channel\Channel;
use Dazzle\Channel\ChannelFactory;
use Dazzle\ChannelSocket\Socket;
use Dazzle\ChannelZmq\ZmqDealer;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Dazzle\Throwable\Exception\Logic\ResourceUndefinedException;
use Dazzle\Throwable\Exception\Logic\InvalidArgumentException;
use Dazzle\Util\Factory\FactoryPluginInterface;
use Exception;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Runtime\RuntimeContextInterface',
        'Dazzle\Loop\LoopInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Dazzle\Channel\Model\ModelFactoryInterface',
        'Dazzle\Channel\Model\ModelInterface',
        'Dazzle\Channel\ChannelFactoryInterface',
        'Dazzle\Channel\ChannelInterface',
        'Dazzle\Channel\ChannelCompositeInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $loop    = $container->make('Dazzle\Loop\LoopInterface');
        $context = $container->make('Kraken\Runtime\RuntimeContextInterface');

        $modelFactory = new ModelFactory($context->getAlias(), $loop);
        $modelFactory
            ->define(Socket::class, function($config = []) use($modelFactory) {
                return new Socket(
                    isset($config['loop']) ? $config['loop'] : $modelFactory->getParam('loop'),
                    array_merge(
                        [
                            'id'        => isset($config['name']) ? $config['name'] : $modelFactory->getParam('name'),
                            'endpoint'  => '',
                            'type'      => Channel::BINDER,
                            'host'      => isset($config['name']) ? $config['name'] : $modelFactory->getParam('name')
                        ],
                        $config
                    )
                );
            })
            ->define(ZmqDealer::class, function($config = []) use($modelFactory) {
                return new ZmqDealer(
                    isset($config['loop']) ? $config['loop'] : $modelFactory->getParam('loop'),
                    array_merge(
                        [
                            'id'        => isset($config['name']) ? $config['name'] : $modelFactory->getParam('name'),
                            'endpoint'  => '',
                            'type'      => Channel::BINDER,
                            'host '     => isset($config['name']) ? $config['name'] : $modelFactory->getParam('name')
                        ],
                        $config
                    )
                );
            })
        ;

        $factory = new ChannelFactory($context->getAlias(), $modelFactory, $loop);

        $container->instance(
            'Dazzle\Channel\Model\ModelFactoryInterface',
            $modelFactory
        );

        $container->factory(
            'Dazzle\Channel\Model\ModelInterface',
            function() use($modelFactory) {
                return $modelFactory->create('Dazzle\Channel\Model\Null\NullModel');
            }
        );

        $container->instance(
            'Dazzle\Channel\ChannelFactoryInterface',
            $factory
        );

        $container->factory(
            'Dazzle\Channel\ChannelInterface',
            [ $factory, 'create' ]
        );

        $container->factory(
            'Dazzle\Channel\ChannelCompositeInterface',
            [ $factory, 'create' ]
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Dazzle\Channel\Model\ModelFactoryInterface'
        );

        $container->remove(
            'Dazzle\Channel\Model\ModelInterface'
        );

        $container->remove(
            'Dazzle\Channel\ChannelFactoryInterface'
        );

        $container->remove(
            'Dazzle\Channel\ChannelInterface'
        );

        $container->remove(
            'Dazzle\Channel\ChannelCompositeInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $factory = $container->make('Dazzle\Channel\Model\ModelFactoryInterface');

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
