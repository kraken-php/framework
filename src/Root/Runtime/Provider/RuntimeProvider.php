<?php

namespace Kraken\Root\Runtime\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;

class RuntimeProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        /** This provider does not necessarily need this interface, however it has to be registered before **/
        'Kraken\Util\System\SystemInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\RuntimeContextInterface',
        'Kraken\Runtime\RuntimeContainerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $runtime = $container->make('Kraken\Runtime\RuntimeContainer');

        $container->instance(
            'Kraken\Runtime\RuntimeContextInterface',
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
            'Kraken\Runtime\RuntimeContextInterface'
        );

        $container->remove(
            'Kraken\Runtime\RuntimeContainerInterface'
        );
    }
}
