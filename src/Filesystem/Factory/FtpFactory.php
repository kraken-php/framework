<?php

namespace Kraken\Filesystem\Factory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Ftp;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Pattern\Factory\SimpleFactoryInterface;

class FtpFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return new Ftp(
            $config
        );
    }
}
