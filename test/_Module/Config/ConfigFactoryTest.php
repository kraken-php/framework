<?php

namespace Kraken\_Unit\Config;

use Kraken\Config\Config;
use Kraken\Config\ConfigFactory;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Test\TModule;
use Kraken\Util\Parser\Json\JsonParser;

/**
 * @runTestsInSeparateProcesses
 */
class ConfigFactoryTest extends TModule
{
    /**
     *
     */
    public function testCaseConstruct_DoesNotThrowsErrors()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        $factory = $this->createConfigFactory();
    }

    /**
     *
     */
    public function testCaseDestruct_DoesNotThrowsErrors()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        $factory = $this->createConfigFactory();
        unset($factory);
    }

    /**
     *
     */
    public function testCaseConfigFactory_CreatesConfig_UsingInternalAndInjectedParsers()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        $factory = $this->createConfigFactory();
        $config = $factory->create([
            Config::getOverwriteHandlerMerger()
        ]);
        $expected = [
            'a' => 'x',
            'b' => [
                'a' => 5,
                'b' => [
                    'a' => 'ABC'
                ],
                'c' => null
            ],
            'd' => [
                'a' => 'A',
                'b' => 'B'
            ],
            'x' => [
                0,
                255
            ]
        ];

        $this->assertSame($expected, $config->getAll());

        unset($config);
        unset($factory);
    }

    /**
     * @return ConfigFactory
     */
    public function createConfigFactory()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        $path = __DIR__ . '/_Data';
        $factory = new FilesystemAdapterFactory();

        $fs = new Filesystem(
            $factory->create('Local', [ [ 'path' => $path ] ])
        );

        $parsers = [
            'json' => new JsonParser()
        ];

        return new ConfigFactory($fs, $parsers);
    }
}
