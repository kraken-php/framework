<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Loop\Loop;

class LoopProvider extends ServiceProvider implements ServiceProviderInterface
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
        'Kraken\Loop\LoopInterface',
        'Kraken\Loop\LoopExtendedInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $config = $container->make('Kraken\Config\ConfigInterface');

        $model = $config->get('loop.model');
        $loop = new Loop(new $model());

        $container->instance(
            'Kraken\Loop\LoopInterface',
            $loop
        );

        $container->instance(
            'Kraken\Loop\LoopExtendedInterface',
            $loop
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Loop\LoopInterface'
        );

        $container->remove(
            'Kraken\Loop\LoopExtendedInterface'
        );
    }
}
