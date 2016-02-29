<?php

namespace Kraken\Console\Client\Provider\Console;

use Kraken\Console\Client\Command\CommandHandler;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Console\Client\Channel\ConsoleInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\Command\CommandHandlerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $channel = $core->make('Kraken\Console\Client\Channel\ConsoleInterface');

        $manager = new CommandHandler($channel, 'ConsoleServer');

        $core->instance(
            'Kraken\Console\Client\Command\CommandHandlerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Console\Client\Command\CommandHandlerInterface'
        );
    }
}
