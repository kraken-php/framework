<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Dazzle\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\AdapterInterface;
use Sabre\DAV\Client;

class WebDavFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return WebDAVAdapter::class;
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
            $this->params($config)
        );

        return new $class($client);
    }
}
