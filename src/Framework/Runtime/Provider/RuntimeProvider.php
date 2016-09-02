<?php

namespace Kraken\Framework\Runtime\Provider;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeContainer');

        $core->instance(
            'Kraken\Core\CoreInputContextInterface',
            $runtime
        );

        $core->instance(
            'Kraken\Runtime\RuntimeContainerInterface',
            $runtime
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Core\CoreInputContextInterface'
        );

        $core->remove(
            'Kraken\Runtime\RuntimeContainerInterface'
        );
    }
}
