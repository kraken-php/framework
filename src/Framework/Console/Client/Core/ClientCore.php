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
    protected function getDefaultProviders()
    {
        return [
            'Kraken\Framework\Core\Provider\ChannelProvider',
            'Kraken\Framework\Core\Provider\ConfigProvider',
            'Kraken\Framework\Core\Provider\ContainerProvider',
            'Kraken\Framework\Core\Provider\CoreProvider',
            'Kraken\Framework\Core\Provider\EnvironmentProvider',
            'Kraken\Framework\Core\Provider\EventProvider',
            'Kraken\Framework\Core\Provider\LogProvider',
            'Kraken\Framework\Core\Provider\LoopProvider',
            'Kraken\Framework\Console\Client\Provider\ChannelProvider',
            'Kraken\Framework\Console\Client\Provider\CommandProvider'
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
