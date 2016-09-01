<?php

namespace Kraken\Framework\Console\Client\Provider;

use Kraken\Console\Client\ClientInterface;
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
        'Kraken\Console\Client\ClientInterface'
    ];

    /**
     * @var ClientInterface
     */
    protected $console;

    /**
     * @param ClientInterface $console
     */
    public function __construct(ClientInterface $console)
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
            'Kraken\Console\Client\ClientInterface',
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
            'Kraken\Console\Client\ClientInterface'
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
