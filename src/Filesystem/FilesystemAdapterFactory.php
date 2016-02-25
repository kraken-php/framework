<?php

namespace Kraken\Filesystem;

use Kraken\Util\Factory\Factory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class FilesystemAdapterFactory extends Factory implements FilesystemAdapterFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $adapters = [
            'Local'     => 'Kraken\Filesystem\Factory\LocalFactory',
            'Ftp'       => 'Kraken\Filesystem\Factory\FtpFactory',
            'Ftpd'      => 'Kraken\Filesystem\Factory\FtpdFactory',
            'Null'      => 'Kraken\Filesystem\Factory\NullFactory',
            'AwsS3v2'   => 'Kraken\Filesystem\Factory\Aws3v2Factory',
            'AwsS3v3'   => 'Kraken\Filesystem\Factory\Aws3v3Factory',
            'Rackspace' => 'Kraken\Filesystem\Factory\RackspaceFactory',
            'Dropbox'   => 'Kraken\Filesystem\Factory\DropboxFactory',
            'Copy'      => 'Kraken\Filesystem\Factory\CopyFactory',
            'Sftp'      => 'Kraken\Filesystem\Factory\SftpFactory',
            'Zip'       => 'Kraken\Filesystem\Factory\ZipFactory',
            'WebDAV'    => 'Kraken\Filesystem\Factory\WebDavFactory',
            'Redis'     => 'Kraken\Filesystem\Factory\RedisFactory',
            'Memory'    => 'Kraken\Filesystem\Factory\MemoryFactory'
        ];

        foreach ($adapters as $name=>$adapter)
        {
            $this->registerAdapter($name, $adapter);
        }
    }

    /**
     * @param string $name
     * @param string|SimpleFactoryInterface $classOrFactory
     */
    protected function registerAdapter($name, $classOrFactory)
    {
        $this
            ->define($name, function($config) use($classOrFactory) {
                if (is_object($classOrFactory))
                {
                    return $classOrFactory->create([ $config ]);
                }
                else
                {
                    return (new $classOrFactory())->create([ $config ]);
                }
            })
        ;
    }
}
