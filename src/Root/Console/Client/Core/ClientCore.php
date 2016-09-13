<?php

namespace Kraken\Root\Console\Client\Core;

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
            'Kraken\Root\Provider\ChannelProvider',
            'Kraken\Root\Provider\ConfigProvider',
            'Kraken\Root\Provider\ContainerProvider',
            'Kraken\Root\Provider\CoreProvider',
            'Kraken\Root\Provider\EnvironmentProvider',
            'Kraken\Root\Provider\EventProvider',
            'Kraken\Root\Provider\LogProvider',
            'Kraken\Root\Provider\LoopProvider',
            'Kraken\Root\Console\Client\Provider\ChannelProvider',
            'Kraken\Root\Console\Client\Provider\CommandProvider',
            'Kraken\Root\Console\Client\Provider\ConsoleProvider',
            'Kraken\Root\Console\Client\Provider\ConsoleBootProvider'
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
