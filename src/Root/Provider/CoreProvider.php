<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;

class CoreProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $container->instance(
            'Kraken\Core\CoreInterface',
            $container
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Core\CoreInterface'
        );
    }
}
