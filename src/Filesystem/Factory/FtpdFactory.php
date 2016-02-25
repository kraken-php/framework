<?php

namespace Kraken\Filesystem\Factory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Ftpd;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class FtpdFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return new Ftpd(
            $config
        );
    }
}
