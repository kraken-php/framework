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
            'Kraken\Core\Provider\Channel\ChannelProvider',
            'Kraken\Core\Provider\Config\ConfigProvider',
            'Kraken\Core\Provider\Container\ContainerProvider',
            'Kraken\Core\Provider\Core\CoreProvider',
            'Kraken\Core\Provider\Core\EnvironmentProvider',
            'Kraken\Core\Provider\Event\EventProvider',
            'Kraken\Core\Provider\Log\LogProvider',
            'Kraken\Core\Provider\Loop\LoopProvider',
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
