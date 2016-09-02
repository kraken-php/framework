<?php

namespace Kraken\Framework\Provider;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Filesystem\FilesystemManager;

class FilesystemProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

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
                    $config['factory'],
                    [ $config['config'] ]
                )
            ));
        }

        $core->instance(
            'Kraken\Filesystem\FilesystemInterface',
            $fsDisk
        );

        $core->instance(
            'Kraken\Filesystem\FilesystemManagerInterface',
            $fsCloud
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Filesystem\FilesystemInterface'
        );

        $core->remove(
            'Kraken\Filesystem\FilesystemManagerInterface'
        );
    }
}
