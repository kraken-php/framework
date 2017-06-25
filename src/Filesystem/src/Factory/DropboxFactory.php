<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Dazzle\Util\Factory\SimpleFactoryInterface;
use Dropbox\Client;
use League\Flysystem\Dropbox\DropboxAdapter;
use League\Flysystem\AdapterInterface;

class DropboxFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return DropboxAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $client = $this->getClient();
        $class  = $this->getClass();

        $client = new $client(
            $this->param($config, 'accessToken'),
            $this->param($config, 'appSecret')
        );

        return new $class(
            $client,
            $this->param($config, 'prefix')
        );
    }
}
