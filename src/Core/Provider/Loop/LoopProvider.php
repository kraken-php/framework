<?php

namespace Kraken\Core\Provider\Loop;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Loop\Loop;

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
        'Kraken\Loop\LoopInterface',
        'Kraken\Loop\LoopExtendedInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $model = $config->get('loop.model');
        $loop = new Loop(new $model());

        $core->instance(
            'Kraken\Loop\LoopInterface',
            $loop
        );

        $core->instance(
            'Kraken\Loop\LoopExtendedInterface',
            $loop
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Loop\LoopInterface'
        );

        $core->remove(
            'Kraken\Loop\LoopExtendedInterface'
        );
    }
}
