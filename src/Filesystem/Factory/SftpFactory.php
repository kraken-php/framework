<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\AdapterInterface;

class SftpFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return SftpAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $class = $this->getClass();

        return new $class(
            $config
        );
    }
}
