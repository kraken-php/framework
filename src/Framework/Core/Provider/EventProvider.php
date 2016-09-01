<?php

namespace Kraken\Framework\Core\Provider;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $emitter = new EventEmitter(
            $core->make('Kraken\Loop\LoopInterface')
        );

        $core->instance(
            'Kraken\Event\EventEmitterInterface',
            $emitter
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Event\EventEmitterInterface'
        );
    }
}
