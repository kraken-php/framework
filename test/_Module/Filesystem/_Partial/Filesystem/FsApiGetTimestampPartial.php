<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\ReadException;

trait FsApiGetTimestampPartial
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
    abstract public function getFullPath($path, $replace = null, $with = '');

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
    public function testApiGetTimestamp_ReturnsTimestamp_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('FILE_A');
        $timestamp = filemtime($this->getFullPath('FILE_A'));

        $test->assertSame($timestamp, $fs->getTimestamp($p));
    }

    /**
     *
     */
    public function testApiGetTimestamp_ReturnsTimestamp_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('DIR_A');
        $timestamp = filemtime($this->getFullPath('DIR_A'));

        $test->assertSame($timestamp, $fs->getTimestamp($p));
    }

    /**
     *
     */
    public function testApiGetTimestamp_ThrowsException_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('NULL');

        $test->setExpectedException(ReadException::class);
        $fs->getTimestamp($p);
    }
}
