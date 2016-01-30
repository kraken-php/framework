<?php

namespace Kraken\Console\Client\Provider\Console;

use Kraken\Console\Client\ConsoleCommandHandler;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Console\Client\ConsoleChannelInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\ConsoleCommandHandlerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $channel = $core->make('Kraken\Console\Client\ConsoleChannelInterface');

        $manager = new ConsoleCommandHandler($channel, 'ConsoleServer');

        $core->instance(
            'Kraken\Console\Client\ConsoleCommandHandlerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Console\Client\ConsoleCommandHandlerInterface'
        );
    }
}
