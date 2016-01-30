<?php

namespace Kraken\Filesystem\Factory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;
use Kraken\Filesystem\Adapter\AdapterLocal;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Pattern\Factory\SimpleFactoryInterface;

class LocalFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [
            'path'          => '',
            'writeFlags'    => LOCK_EX,
            'linkHandling'  => Local::DISALLOW_LINKS,
            'permissions'   => []
        ];
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        return new AdapterLocal(
            $this->param($config, 'path'),
            $this->param($config, 'writeFlags'),
            $this->param($config, 'linkHandling'),
            $this->param($config, 'permissions')
        );
    }

}
