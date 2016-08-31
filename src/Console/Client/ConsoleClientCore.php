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
    protected function getDefaultProviders()
    {
        return [
            'Kraken\Core\Provider\Channel\ChannelProvider',
            'Kraken\Core\Provider\Config\ConfigProvider',
            'Kraken\Core\Provider\Container\ContainerProvider',
            'Kraken\Core\Provider\Core\CoreProvider',
            'Kraken\Core\Provider\Environment\EnvironmentProvider',
            'Kraken\Core\Provider\Event\EventProvider',
            'Kraken\Core\Provider\Log\LogProvider',
            'Kraken\Core\Provider\Loop\LoopProvider',
            'Kraken\Console\Client\Provider\Channel\ChannelProvider',
            'Kraken\Console\Client\Provider\Command\CommandProvider'
        ];
    }

    /**
     * @return string[]
     */
    protected function getDefaultAliases()
    {
        return [];
    }
}
