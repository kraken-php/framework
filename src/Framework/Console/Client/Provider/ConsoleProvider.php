<?php

namespace Kraken\Framework\Console\Client\Provider;

use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Core\CoreInterface;

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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $console = $core->make('Kraken\Console\Client\Client');

        $core->instance(
            'Kraken\Core\CoreInputContextInterface',
            $console
        );

        $core->instance(
            'Kraken\Console\Client\ClientInterface',
            $console
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
            'Kraken\Console\Client\ClientInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $console = $core->make('Kraken\Console\Client\ClientInterface');
        $loop    = $core->make('Kraken\Loop\LoopExtendedInterface');

        $console->setLoop($loop);
    }
}
