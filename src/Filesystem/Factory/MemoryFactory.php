<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\Memory\MemoryAdapter;
use League\Flysystem\AdapterInterface;

class MemoryFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return MemoryAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $class = $this->getClass();

        return new $class();
    }
}
