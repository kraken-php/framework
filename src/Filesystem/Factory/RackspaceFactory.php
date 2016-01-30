<?php

namespace Kraken\Filesystem\Factory;

use OpenCloud\OpenStack;
use OpenCloud\Rackspace;
use League\Flysystem\Filesystem;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Pattern\Factory\SimpleFactoryInterface;

class RackspaceFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [
            'identityEndpoint' => Rackspace::UK_IDENTITY_ENDPOINT,
            'serviceName'      => 'CloudFiles',
            'serviceRegion'    => 'LON',
            'serviceUrlType'   => null
        ];
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $client = new Rackspace(
            $this->param($config, 'identityEndpoint'),
            $this->params($config)
        );

        $store = $client->objectStoreService(
            $this->param($config, 'serviceName'),
            $this->param($config, 'serviceRegion'),
            $this->param($config, 'serviceUrlType')
        );

        return new RackspaceAdapter(
            $store->getContainer('flysystem')
        );
    }
}
