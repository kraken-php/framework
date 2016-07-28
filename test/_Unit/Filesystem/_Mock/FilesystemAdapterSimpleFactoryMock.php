<?php

namespace Kraken\_Unit\Filesystem\_Mock;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use League\Flysystem\AdapterInterface;

class FilesystemAdapterSimpleFactoryMock extends FilesystemAdapterSimpleFactory
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
        return '';
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return '';
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        return new FilesystemAdapterMock($config);
    }
}
