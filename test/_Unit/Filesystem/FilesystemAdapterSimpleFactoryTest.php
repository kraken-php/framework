<?php

namespace Kraken\_Unit\Filesystem;

use Kraken\_Unit\Filesystem\_Mock\FilesystemAdapterSimpleFactoryMock;
use Kraken\Filesystem\FilesystemAdapterSimpleFactory;
use Kraken\Test\TUnit;
use StdClass;

class FilesystemAdapterSimpleFactoryTest extends TUnit
{
    /**
     *
     */
    public function testApiParam_ReturnsParam_WhenParamExists()
    {
        $std = new StdClass;
        $factory = $this->createFilesystemAdapterSimpleFactory();

        $this->assertSame($std, $this->callProtectedMethod($factory, 'param', [ [ 'key' => $std ], 'key' ]));
    }

    /**
     *
     */
    public function testApiParam_ReturnsDefault_WhenParamDoesNotExistButDefaultDoes()
    {
        $std = new StdClass;
        $factory = $this->createFilesystemAdapterSimpleFactory([ 'key' => $std ]);

        $this->assertSame($std, $this->callProtectedMethod($factory, 'param', [ [], 'key' ]));
    }

    /**
     *
     */
    public function testApiParam_ReturnsNull_WhenParamDoesNotExistAndDefaultDoesNotToo()
    {
        $factory = $this->createFilesystemAdapterSimpleFactory();

        $this->assertSame(null, $this->callProtectedMethod($factory, 'param', [ [], 'key' ]));
    }

    /**
     *
     */
    public function testApiParam_ReturnsMergedParamsAndDefaults()
    {
        $factory = $this->createFilesystemAdapterSimpleFactory([
            'param1' => $s1 = new StdClass,
            'param2' => $s2 = new StdClass
        ]);

        $result = $this->callProtectedMethod($factory, 'params', [[
            'param2' => $s3 = new StdClass,
            'param3' => $s4 = new StdClass
        ]]);

        $expected = [
            'param1' => $s1,
            'param2' => $s3,
            'param3' => $s4
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @param mixed[] $defaults
     * @return FilesystemAdapterSimpleFactory
     */
    public function createFilesystemAdapterSimpleFactory($defaults = [])
    {
        return new FilesystemAdapterSimpleFactoryMock($defaults);
    }
}
