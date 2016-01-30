<?php

namespace Kraken\Filesystem\Factory;

use Barracuda\Copy\API;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Copy\CopyAdapter;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Pattern\Factory\SimpleFactoryInterface;

class CopyFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        $client = new API(
            $this->param($config, 'consumerKey'),
            $this->param($config, 'consumerSecret'),
            $this->param($config, 'accessToken'),
            $this->param($config, 'tokenSecret')
        );

        return new CopyAdapter(
            $client,
            $this->param($config, 'prefix')
        );
    }
}
