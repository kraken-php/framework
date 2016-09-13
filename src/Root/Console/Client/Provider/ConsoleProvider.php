<?php

namespace Kraken\Root\Console\Client\Provider;

use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Container\ContainerInterface;

class ConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\RuntimeContextInterface',
        'Kraken\Console\Client\ClientInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $console = $container->make('Kraken\Console\Client\Client');

        $container->instance(
            'Kraken\Runtime\RuntimeContextInterface',
            $console
        );

        $container->instance(
            'Kraken\Console\Client\ClientInterface',
            $console
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Runtime\RuntimeContextInterface'
        );

        $container->remove(
            'Kraken\Console\Client\ClientInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function boot(ContainerInterface $container)
    {
        $console = $container->make('Kraken\Console\Client\ClientInterface');
        $loop    = $container->make('Kraken\Loop\LoopExtendedInterface');

        $console->setLoop($loop);
    }
}
