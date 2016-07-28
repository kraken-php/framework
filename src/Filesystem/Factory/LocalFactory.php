<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\Adapter\AdapterLocal;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

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
        return AdapterLocal::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $class = $this->getClass();

        return new $class(
            $this->param($config, 'path'),
            $this->param($config, 'writeFlags'),
            $this->param($config, 'linkHandling'),
            $this->param($config, 'permissions')
        );
    }

}
