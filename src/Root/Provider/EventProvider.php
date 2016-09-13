<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Event\EventEmitter;

class EventProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Loop\LoopInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Event\EventEmitterInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $emitter = new EventEmitter(
            $container->make('Kraken\Loop\LoopInterface')
        );

        $container->instance(
            'Kraken\Event\EventEmitterInterface',
            $emitter
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Event\EventEmitterInterface'
        );
    }
}
