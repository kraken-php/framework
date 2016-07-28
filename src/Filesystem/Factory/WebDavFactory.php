<?php

namespace Kraken\Filesystem\Factory;

use Sabre\DAV\Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

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
