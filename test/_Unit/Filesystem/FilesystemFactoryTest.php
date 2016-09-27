<?php

namespace Kraken\_Unit\Filesystem;

use Kraken\Filesystem\FilesystemFactory;
use Kraken\Filesystem\FilesystemFactoryInterface;
use Kraken\Test\TUnit;

class FilesystemFactoryTest extends TUnit
{
    /**
     *
     */
    public function testCaseFilesystemFactory_HasDefinitionsForAllFactories()
    {
        $factory = $this->createFilesystemFactory();
        $factoryClasses = $this->getAllSupportedFactories();

        foreach ($factoryClasses as $key=>$factoryClass)
        {
            $this->assertTrue($factory->hasDefinition($key));
        }
    }

    /**
     * @return FilesystemFactoryInterface
     */
    public function createFilesystemFactory()
    {
        return new FilesystemFactory();
    }

    /**
     * @return string[]
     */
    public function getAllSupportedFactories()
    {
        return [
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
    }
}
