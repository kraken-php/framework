<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;
use Danhunsaker\Flysystem\Redis\RedisAdapter;
use League\Flysystem\AdapterInterface;
use Predis\Client;

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
     * @return string
     */
    protected function getClient()
    {
        return Client::class;
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return RedisAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $client = $this->getClient();
        $class  = $this->getClass();

        $redis = new $client(
            $this->params($config)
        );

        return new $class($redis);
    }
}
