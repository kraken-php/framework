<?php

namespace Kraken\Filesystem\Factory;

use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Dazzle\Util\Factory\SimpleFactoryInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\AdapterInterface;
use OpenCloud\Rackspace;

class RackspaceFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [
            'identityEndpoint' => class_exists(Rackspace::class) ? Rackspace::UK_IDENTITY_ENDPOINT : null,
            'serviceName'      => 'CloudFiles',
            'serviceRegion'    => 'LON',
            'serviceUrlType'   => null
        ];
    }

    /**
     * @return string
     */
    protected function getClient()
    {
        return Rackspace::class;
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return RackspaceAdapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $client = $this->getClient();
        $class  = $this->getClass();

        $client = new $client(
            $this->param($config, 'identityEndpoint'),
            $this->params($config)
        );

        $store = $client->objectStoreService(
            $this->param($config, 'serviceName'),
            $this->param($config, 'serviceRegion'),
            $this->param($config, 'serviceUrlType')
        );

        return new $class(
            $store->getContainer('flysystem')
        );
    }
}
