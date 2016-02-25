<?php

namespace Kraken\Filesystem\Factory;

use Danhunsaker\Flysystem\Redis\RedisAdapter;
use Predis\Client;
use League\Flysystem\AdapterInterface;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class RedisFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [];
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $redis = new Client(
            $this->params($config)
        );

        return new RedisAdapter($redis);
    }
}
