<?php

namespace Kraken\Console\Client\Provider\Console;

use Kraken\Console\Client\ConsoleClientInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class ConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Console\Client\ConsoleClientInterface'
    ];

    /**
     * @var ConsoleClientInterface
     */
    protected $console;

    /**
     * @param ConsoleClientInterface $console
     */
    public function __construct(ConsoleClientInterface $console)
    {
        $this->console = $console;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->console);
    }

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core->instance(
            'Kraken\Core\CoreInputContextInterface',
            $this->console
        );

        $core->instance(
            'Kraken\Console\Client\ConsoleClientInterface',
            $this->console
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
            'Kraken\Console\Client\ConsoleClientInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $console = $this->console;
        $loop    = $core->make('Kraken\Loop\LoopExtendedInterface');

        $console->setLoop($loop);
    }
}
