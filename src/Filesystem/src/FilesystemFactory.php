<?php

namespace Kraken\Filesystem;

use Dazzle\Util\Factory\Factory;
use Dazzle\Util\Factory\SimpleFactoryInterface;

class FilesystemFactory extends Factory implements FilesystemFactoryInterface
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
            $this->registerFactoryMethod($name, $adapter);
        }
    }

    /**
     * Register existing or lazy factory as factory method for adapter under specified key.
     *
     * @param string $name
     * @param string|SimpleFactoryInterface $classOrFactory
     */
    protected function registerFactoryMethod($name, $classOrFactory)
    {
        $this
            ->define($name, function($config = []) use($classOrFactory) {
                $factory = is_object($classOrFactory) ? $classOrFactory : new $classOrFactory();

                return new Filesystem(
                    $factory->create([ $config ])
                );
            })
        ;
    }
}
