<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;

trait FsApiIsDirPartial
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
    public function testApiIsDir_ReturnsTrue_WhenItDoesExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertTrue($fs->isDir($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiIsDir_ReturnsFalse_WhenItDoesExistButIsNotDir()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertFalse($fs->isDir($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiIsDir_ThrowsException_WhenItDoesNotExist()
    {
        $test = $this->getTest();
        $test->setExpectedException(IoReadException::class);

        $fs = $this->createFilesystem();

        $fs->isDir($this->getPrefixed('NULL'));
    }

}
