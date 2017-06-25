<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Dazzle\Loop\Loop;

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
        'Dazzle\Loop\LoopInterface',
        'Dazzle\Loop\LoopExtendedInterface'
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
            'Dazzle\Loop\LoopInterface',
            $loop
        );

        $container->instance(
            'Dazzle\Loop\LoopExtendedInterface',
            $loop
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Dazzle\Loop\LoopInterface'
        );

        $container->remove(
            'Dazzle\Loop\LoopExtendedInterface'
        );
    }
}
