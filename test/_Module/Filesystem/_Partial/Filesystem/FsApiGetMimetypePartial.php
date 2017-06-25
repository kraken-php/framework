<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\ReadException;

trait FsApiGetMimetypePartial
{
    /**
     * @see TestCase::getTest
     * @return TModule
     */
    abstract public function getTest();

    /**
     * @return string
     */
    abstract public function getPrefix();

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    abstract public function getPrefixed($path, $replace = null, $with = '');

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    abstract public function getPath($path, $replace = null, $with = '');

    /**
     * @param string $path
     * @param string $typeFilter
     * @param bool $recursive
     * @param string $filePattern
     * @return array
     */
    abstract public function getPathData($path = '', $typeFilter = '', $recursive = false, $filePattern = '');

    /**
     * @return FilesystemInterface
     */
    abstract public function createFilesystem();

    /**
     *
     */
    public function testApiGetMimetype_ReturnsMimetype_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('FILE_A');
        $mime = 'text/plain';

        $test->assertSame($mime, $fs->getMimetype($p));
    }

    /**
     *
     */
    public function testApiGetMimetype_ReturnsMimetype_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('DIR_A');
        $mime = 'directory';

        $test->assertSame($mime, $fs->getMimetype($p));
    }

    /**
     *
     */
    public function testApiGetMimetype_ThrowsException_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('NULL');

        $test->setExpectedException(ReadException::class);
        $fs->getMimetype($p);
    }
}
