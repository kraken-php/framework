<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

trait FsApiRemoveDirPartial
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
    public function testApiRemoveDir_ThrowsException_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $pc = $this->getPrefixed('FILE_C');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($pc));
        $fs->removeDir($pc);
    }

    /**
     *
     */
    public function testApiRemoveDir_RemovesDir_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $pc = $this->getPrefixed('FILE_C');
        $d = $this->getPrefixed('DIR_A');

        $test->assertTrue($fs->exists($pc));
        $test->assertTrue($fs->exists($d));
        $fs->removeDir($d);

        $test->assertFalse($fs->exists($pc));
        $test->assertFalse($fs->exists($d));
    }

    /**
     *
     */
    public function testApiRemoveDir_ThrowsException_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $null = $this->getPrefixed('NULL');

        $test->setExpectedException(IoWriteException::class);
        $test->assertFalse($fs->exists($null));
        $fs->removeDir($null);
    }
}
