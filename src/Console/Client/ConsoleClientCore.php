<?php

namespace Kraken\Console\Client;

use Kraken\Core\Core;
use Kraken\Core\CoreInterface;

class ConsoleClientCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = 'Console';

    /**
     * @return string[]
     */
    protected function defaultProviders()
    {
        return [
            'Kraken\Provider\Channel\ChannelProvider',
            'Kraken\Provider\Config\ConfigProvider',
            'Kraken\Provider\Container\ContainerProvider',
            'Kraken\Provider\Core\CoreProvider',
            'Kraken\Provider\Core\EnvironmentProvider',
            'Kraken\Provider\Event\EventProvider',
            'Kraken\Provider\Log\LogProvider',
            'Kraken\Provider\Loop\LoopProvider',
            'Kraken\Console\Client\Provider\Channel\ChannelProvider',
            'Kraken\Console\Client\Provider\Command\CommandProvider',
            'Kraken\Console\Client\Provider\Console\SymfonyProvider',
            'Kraken\Console\Client\Provider\Console\CommandProvider'
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultAliases()
    {
        return [];
    }
}
