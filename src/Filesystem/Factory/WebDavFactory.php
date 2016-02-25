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
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $client = new Client(
            $this->params($config)
        );

        return new WebDAVAdapter($client);
    }
}
