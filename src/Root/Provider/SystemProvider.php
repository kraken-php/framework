<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Runtime\Runtime;
use Kraken\Util\Isolate\Isolate;
use Kraken\Util\System\SystemUnix;

class SystemProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Util\System\SystemInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $isolate = $container->getType() === Runtime::UNIT_PROCESS ? new Isolate() : null;
        $system  = new SystemUnix($isolate);

        $container->instance(
            'Kraken\Util\System\SystemInterface',
            $system
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Util\System\SystemInterface'
        );
    }
}
