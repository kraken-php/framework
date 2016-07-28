<?php

namespace Kraken\_Unit\Filesystem;

use Kraken\_Unit\Filesystem\_Mock\FilesystemAdapterMock;
use Kraken\_Unit\Filesystem\_Mock\FilesystemAdapterSimpleFactoryMock;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Filesystem\FilesystemAdapterFactoryInterface;
use Kraken\Test\TUnit;

class FilesystemAdapterFactoryTest extends TUnit
{
    /**
     *
     */
    public function testCaseFilesystemAdapterFactory_HasDefinitionsForAllFactories()
    {
        $factory = $this->createFilesystemAdapterFactory();
        $factoryClasses = $this->getAllSupportedFactories();

        foreach ($factoryClasses as $key=>$factoryClass)
        {
            $this->assertTrue($factory->hasDefinition($key));
        }
    }

    /**
     *
     */
    public function testApiRegisterAdapter_RegistersAdapter_WhenInitializedAdapterSet()
    {
        $factory = $this->createFilesystemAdapterFactory();
        $key = 'Other';
        $adapterFactory = new FilesystemAdapterSimpleFactoryMock();
        $config = [
            'param1' => 'ARG_PARAM_1',
            'param2' => null,
            'param3' => 0
        ];

        $this->assertFalse($factory->hasDefinition($key));

        $this->callProtectedMethod($factory, 'registerAdapter', [ $key, $adapterFactory ]);
        $adapter = $factory->create($key, [ $config ]);

        $this->assertInstanceOf(FilesystemAdapterMock::class, $adapter);
        $this->assertSame($config, $adapter->getArgs()[0]);
    }

    /**
     *
     */
    public function testApiRegisterAdapter_RegistersAdapter_WhenClassNameSet()
    {
        $factory = $this->createFilesystemAdapterFactory();
        $key = 'Other';
        $adapterFactory = FilesystemAdapterSimpleFactoryMock::class;
        $config = [
            'param1' => 'ARG_PARAM_1',
            'param2' => null,
            'param3' => 0
        ];

        $this->assertFalse($factory->hasDefinition($key));

        $this->callProtectedMethod($factory, 'registerAdapter', [ $key, $adapterFactory ]);
        $adapter = $factory->create($key, [ $config ]);

        $this->assertInstanceOf(FilesystemAdapterMock::class, $adapter);
        $this->assertSame($config, $adapter->getArgs()[0]);
    }

    /**
     * @return FilesystemAdapterFactoryInterface
     */
    public function createFilesystemAdapterFactory()
    {
        return new FilesystemAdapterFactory();
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
