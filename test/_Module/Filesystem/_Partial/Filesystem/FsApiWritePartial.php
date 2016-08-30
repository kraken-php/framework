<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\WriteException;

trait FsApiWritePartial
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
    public function testApiWrite_WritesFile_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('FILE_A');
        $before = 'FILE_A_TEXT';
        $after  = 'FILE_A_NEW_TEXT';

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($before, $fs->read($dest));
        $fs->write($dest, $after);

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($after, $fs->read($dest));
    }

    /**
     *
     */
    public function testApiWrite_ThrowsException_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('DIR_A');
        $after  = 'FILE_A_NEW_TEXT';

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($dest));
        $fs->write($dest, $after);
    }

    /**
     *
     */
    public function testApiWrite_ThrowsException_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('NULL');
        $text = 'FILE_A_NEW_TEXT';

        $test->setExpectedException(WriteException::class);
        $test->assertFalse($fs->exists($dest));
        $fs->write($dest, $text);
    }
}
