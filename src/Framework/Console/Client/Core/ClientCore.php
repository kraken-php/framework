<?php

namespace Kraken\Framework\Console\Client\Core;

use Kraken\Core\Core;
use Kraken\Core\CoreInterface;

class ClientCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = 'Console';

    /**
     * @return string[]
     */
    public function getDefaultProviders()
    {
        return [
            'Kraken\Framework\Provider\ChannelProvider',
            'Kraken\Framework\Provider\ConfigProvider',
            'Kraken\Framework\Provider\ContainerProvider',
            'Kraken\Framework\Provider\CoreProvider',
            'Kraken\Framework\Provider\EnvironmentProvider',
            'Kraken\Framework\Provider\EventProvider',
            'Kraken\Framework\Provider\LogProvider',
            'Kraken\Framework\Provider\LoopProvider',
            'Kraken\Framework\Console\Client\Provider\ChannelProvider',
            'Kraken\Framework\Console\Client\Provider\CommandProvider',
            'Kraken\Framework\Console\Client\Provider\ConsoleProvider',
            'Kraken\Framework\Console\Client\Provider\ConsoleBootProvider'
        ];
    }

    /**
     * @return string[]
     */
    public function getDefaultAliases()
    {
        return [];
    }
}
