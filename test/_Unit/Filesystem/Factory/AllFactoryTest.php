<?php

namespace Kraken\_Unit\Filesystem\Factory;

use Kraken\_Unit\Filesystem\_Mock\FilesystemAdapterFactoryMock;
use Kraken\Filesystem\Factory\Aws3v2Factory;
use Kraken\Filesystem\Factory\Aws3v3Factory;
use Kraken\Filesystem\Factory\DropboxFactory;
use Kraken\Filesystem\Factory\FtpdFactory;
use Kraken\Filesystem\Factory\FtpFactory;
use Kraken\Filesystem\Factory\LocalFactory;
use Kraken\Filesystem\Factory\MemoryFactory;
use Kraken\Filesystem\Factory\NullFactory;
use Kraken\Filesystem\Factory\RackspaceFactory;
use Kraken\Filesystem\Factory\RedisFactory;
use Kraken\Filesystem\Factory\SftpFactory;
use Kraken\Filesystem\Factory\WebDavFactory;
use Kraken\Filesystem\Factory\ZipFactory;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Test\TUnit;
use OpenCloud\Rackspace;

class AllFactoryTest extends TUnit
{
    /**
     * @dataProvider factoriesProvider
     */
    public function testApiGetDefaults_ReturnsDefaultProperties($class, $config)
    {
        $factory = $this->createFactory($class);
        $expected = $config['getDefaults'];

        $this->assertSame($expected, $this->callProtectedMethod($factory, 'getDefaults'));
    }

    /**
     * @dataProvider factoriesProvider
     */
    public function testApiGetClient_ReturnsClassName($class, $config)
    {
        $factory = $this->createFactory($class, [], [ false, true ]);
        $expected = $config['getClient'];

        $this->assertEquals($expected, $this->callProtectedMethod($factory, 'getClient'));
    }

    /**
     * @dataProvider factoriesProvider
     */
    public function testApiGetClass_ReturnsClassName($class, $config)
    {
        $factory = $this->createFactory($class, [], [ true, false ]);
        $expected = $config['getClass'];

        $this->assertEquals($expected, $this->callProtectedMethod($factory, 'getClass'));
    }

    /**
     * @dataProvider factoriesProvider
     */
    public function testApiOnCreate_GetsDefaultProperties($class, $config)
    {
        $args = $config['getArgs'][0];
        $factory = $this->createFactory($class, $args);
        $expected = $config['getArgs'][1];

        $adapter = $this->callProtectedMethod($factory, 'onCreate');

        $this->assertSame($expected, $adapter->getArgs());
    }

    /**
     * @return array
     */
    public function factoriesProvider()
    {
        return [
            [ Aws3v2Factory::class, $this->getAws3v2Data() ],
            [ Aws3v3Factory::class, $this->getAws3v3Data() ],
            [ DropboxFactory::class, $this->getDropboxData() ],
            [ FtpdFactory::class, $this->getFtpdData() ],
            [ FtpFactory::class, $this->getFtpData() ],
            [ LocalFactory::class, $this->getLocalData() ],
            [ MemoryFactory::class, $this->getMemoryData() ],
            [ NullFactory::class, $this->getNullData() ],
            [ RackspaceFactory::class, $this->getRackspaceData() ],
            [ RedisFactory::class, $this->getRedisData() ],
            [ SftpFactory::class, $this->getSftpData() ],
            [ WebDavFactory::class, $this->getWebDavData() ],
            [ ZipFactory::class, $this->getZipData() ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getAws3v2Data()
    {
        return [
            'getDefaults'   => [
                'bucket'    => '',
                'prefix'    => null,
                'options'   => []
            ],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\AwsS3v2\AwsS3Adapter',
            'getArgs'       => [
                [
                    'bucket'    => 'ARG_BUCKET',
                    'prefix'    => 'ARG_PREFIX',
                    'options'   => 'ARG_OPTIONS'
                ],
                [
                    class_exists('Aws\S3\S3Client') ? 'Aws\S3\S3Client' : null,
                    'ARG_BUCKET',
                    'ARG_PREFIX',
                    'ARG_OPTIONS'
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getAws3v3Data()
    {
        return [
            'getDefaults'   => [
                'bucket'    => '',
                'prefix'    => '',
                'options'   => []
            ],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\AwsS3v3\AwsS3Adapter',
            'getArgs'       => [
                [
                    'bucket'    => 'ARG_BUCKET',
                    'prefix'    => 'ARG_PREFIX',
                    'options'   => 'ARG_OPTIONS'
                ],
                [
                    class_exists('Aws\S3\S3Client') ? 'Aws\S3\S3Client' : null,
                    'ARG_BUCKET',
                    'ARG_PREFIX',
                    'ARG_OPTIONS'
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getDropboxData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => 'Dropbox\Client',
            'getClass'      => 'League\Flysystem\Dropbox\DropboxAdapter',
            'getArgs'       => [
                [
                    'prefix' => 'ARG_PREFIX'
                ],
                [
                    FilesystemAdapterFactoryMock::class,
                    'ARG_PREFIX'
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getFtpdData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\Adapter\Ftpd',
            'getArgs'       => [
                [],
                [
                    []
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getFtpData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\Adapter\Ftp',
            'getArgs'       => [
                [],
                [
                    []
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getLocalData()
    {
        return [
            'getDefaults'   => [
                'path'          => '',
                'writeFlags'    => LOCK_EX,
                'linkHandling'  => 0002,
                'permissions'   => []
            ],
            'getClient'     => '',
            'getClass'      => 'Kraken\Filesystem\Adapter\AdapterLocal',
            'getArgs'       => [
                [
                    'path'          => 'ARG_PATH',
                    'writeFlags'    => 'ARG_FLAGS',
                    'linkHandling'  => 'ARG_LINK_HANDLING',
                    'permissions'   => 'ARG_PERMISSIONS'
                ],
                [
                    'ARG_PATH',
                    'ARG_FLAGS',
                    'ARG_LINK_HANDLING',
                    'ARG_PERMISSIONS'
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getMemoryData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\Memory\MemoryAdapter',
            'getArgs'       => [
                [],
                []
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNullData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\Adapter\NullAdapter',
            'getArgs'       => [
                [],
                []
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getRackspaceData()
    {
        return [
            'getDefaults'   => [
                'identityEndpoint' => class_exists(Rackspace::class) ? Rackspace::UK_IDENTITY_ENDPOINT : null,
                'serviceName'      => 'CloudFiles',
                'serviceRegion'    => 'LON',
                'serviceUrlType'   => null
            ],
            'getClient'     => 'OpenCloud\Rackspace',
            'getClass'      => 'League\Flysystem\Rackspace\RackspaceAdapter',
            'getArgs'       => [
                [],
                [
                    FilesystemAdapterFactoryMock::class
                ]
            ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function getRedisData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => 'Predis\Client',
            'getClass'      => 'Danhunsaker\Flysystem\Redis\RedisAdapter',
            'getArgs'       => [
                [],
                [
                    FilesystemAdapterFactoryMock::class
                ]
            ]
        ];
    }

    /**
     *
     */
    public function getSftpData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\Sftp\SftpAdapter',
            'getArgs'       => [
                [],
                [
                    []
                ]
            ]
        ];
    }

    /**
     *
     */
    public function getWebDavData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => 'Sabre\DAV\Client',
            'getClass'      => 'League\Flysystem\WebDAV\WebDAVAdapter',
            'getArgs'       => [
                [],
                [
                    FilesystemAdapterFactoryMock::class
                ]
            ]
        ];
    }

    /**
     *
     */
    public function getZipData()
    {
        return [
            'getDefaults'   => [],
            'getClient'     => '',
            'getClass'      => 'League\Flysystem\ZipArchive\ZipArchiveAdapter',
            'getArgs'       => [
                [
                    'path' => 'ARG_PATH'
                ],
                [
                    'ARG_PATH'
                ]
            ]
        ];
    }

    /**
     * @param string $class
     * @param mixed[] $args
     * @param bool[] $replaces
     * @return FilesystemAdapterSimpleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createFactory($class, $args = [], $replaces = [ true, true ])
    {
        $methods = [];

        if ($replaces[0])
        {
            $methods[] = 'getClient';
        }

        if ($replaces[1])
        {
            $methods[] = 'getClass';
        }

        $args = $args ? [ $args ] : [];
        $mock = $this->getMock($class, $methods, $args);

        if ($replaces[0])
        {
            $mock
                ->expects($this->any())
                ->method('getClient')
                ->will($this->returnValue(FilesystemAdapterFactoryMock::class));
        }

        if ($replaces[1])
        {
            $mock
                ->expects($this->any())
                ->method('getClass')
                ->will($this->returnValue(FilesystemAdapterFactoryMock::class));
        }

        return $mock;
    }
}
