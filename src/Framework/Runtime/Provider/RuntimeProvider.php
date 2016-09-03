<?php

namespace Kraken\Framework\Runtime\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;

class RuntimeProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Runtime\RuntimeContainerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $runtime = $container->make('Kraken\Runtime\RuntimeContainer');

        $container->instance(
            'Kraken\Core\CoreInputContextInterface',
            $runtime
        );

        $container->instance(
            'Kraken\Runtime\RuntimeContainerInterface',
            $runtime
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Core\CoreInputContextInterface'
        );

        $container->remove(
            'Kraken\Runtime\RuntimeContainerInterface'
        );
    }
}
