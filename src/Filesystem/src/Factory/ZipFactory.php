<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Dazzle\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use League\Flysystem\AdapterInterface;

class ZipFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return ZipArchiveAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $class = $this->getClass();
        return new $class(
            $this->param($config, 'path')
        );
    }
}
