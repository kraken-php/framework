<?php

namespace Kraken\Filesystem\Factory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Pattern\Factory\SimpleFactoryInterface;

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
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        return new ZipArchiveAdapter(
            $this->param($config, 'path')
        );
    }
}
