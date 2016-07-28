<?php

namespace Kraken\Filesystem\Factory;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v2\AwsS3Adapter;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class Aws3v2Factory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [
            'bucket'    => '',
            'prefix'    => null,
            'options'   => []
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
        return AwsS3Adapter::class;
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $class = $this->getClass();

        return new $class(
            class_exists(S3Client::class) ? S3Client::factory($this->params($config)) : null,
            $this->param($config, 'bucket'),
            $this->param($config, 'prefix'),
            $this->param($config, 'options')
        );
    }
}
