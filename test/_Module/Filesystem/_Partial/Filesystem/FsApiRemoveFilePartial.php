<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\WriteException;

trait FsApiRemoveFilePartial
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
    public function testApiRemoveFile_RemovesNode_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('FILE_A');

        $test->assertTrue($fs->exists($p));
        $fs->removeFile($p);
        $test->assertFalse($fs->exists($p));
    }

    /**
     *
     */
    public function testApiRemoveFile_RemovesNode_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('DIR_A');

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($p));
        $fs->removeFile($p);
    }

    /**
     *
     */
    public function testApiRemoveFile_RemovesNode_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('NULL');

        $test->setExpectedException(WriteException::class);
        $test->assertFalse($fs->exists($p));
        $fs->removeFile($p);
    }
}
