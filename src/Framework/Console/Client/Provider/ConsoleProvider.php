<?php

namespace Kraken\Framework\Console\Client\Provider;

use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Container\ContainerInterface;

class ConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Console\Client\ClientInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $console = $container->make('Kraken\Console\Client\Client');

        $container->instance(
            'Kraken\Core\CoreInputContextInterface',
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
            'Kraken\Core\CoreInputContextInterface'
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
