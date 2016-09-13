<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Filesystem\FilesystemManager;

class FilesystemProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInterface',
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Filesystem\FilesystemInterface',
        'Kraken\Filesystem\FilesystemManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $core   = $container->make('Kraken\Core\CoreInterface');
        $config = $container->make('Kraken\Config\ConfigInterface');

        $factory = new FilesystemAdapterFactory();
        $fsCloud = new FilesystemManager();
        $fsDisk  = new Filesystem(
            $factory->create('Local', [ [ 'path' => $core->getBasePath() ] ])
        );

        $disks = $config->get('filesystem.cloud');

        foreach ($disks as $disk=>$config)
        {
            $fsCloud->mountFilesystem($disk, new Filesystem(
                $factory->create(
                    $config['class'],
                    [ $config['config'] ]
                )
            ));
        }

        $container->instance(
            'Kraken\Filesystem\FilesystemInterface',
            $fsDisk
        );

        $container->instance(
            'Kraken\Filesystem\FilesystemManagerInterface',
            $fsCloud
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Filesystem\FilesystemInterface'
        );

        $container->remove(
            'Kraken\Filesystem\FilesystemManagerInterface'
        );
    }
}
