<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Util\Isolate\Isolate;

class IsolateProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Util\Isolate\IsolateInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $isolate = new Isolate();

        $container->instance(
            'Kraken\Util\Isolate\IsolateInterface',
            $isolate
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Util\Isolate\IsolateInterface'
        );
    }
}
