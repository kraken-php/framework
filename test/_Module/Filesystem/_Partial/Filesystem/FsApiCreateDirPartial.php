<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\WriteException;

trait FsApiCreateDirPartial
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
    public function testApiCreateDir_ThrowsException_WhenNodeExistAndIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('FILE_A');

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($p));
        $fs->createDir($p);
    }

    /**
     *
     */
    public function testApiCreateDir_CreatesDirectory_WhenNodeExistAndIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $p = $this->getPrefixed('DIR_A');
        $d = $this->getPrefixed('FILE_C');

        $test->assertTrue($fs->exists($p));
        $test->assertTrue($fs->exists($d));
        $fs->createDir($p);

        $test->assertTrue($fs->exists($p));
        $test->assertFalse($fs->exists($d));
    }

    /**
     *
     */
    public function testApiCreateDir_CreatesDir_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $null = $this->getPrefixed('DIR_NULL');

        $test->assertFalse($fs->exists($null));
        $fs->createDir($null);

        $test->assertTrue($fs->exists($null));
        $test->assertTrue($fs->isDir($null));
    }

    /**
     *
     */
    public function testApiCreateDir_CreatesPublicDir_WhenVisibilityFlagSetToPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $null = $this->getPrefixed('DIR_NULL');

        $test->assertFalse($fs->exists($null));
        $fs->createDir($null, Filesystem::VISIBILITY_PUBLIC);

        $test->assertTrue($fs->exists($null));
        $test->assertTrue($fs->isPublic($null));
    }

    /**
     *
     */
    public function testApiCreateDir_CreatesPrivateDir_WhenVisibilityFlagSetToPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $null = $this->getPrefixed('DIR_NULL');

        $test->assertFalse($fs->exists($null));
        $fs->createDir($null, Filesystem::VISIBILITY_PRIVATE);

        $test->assertTrue($fs->exists($null));
        $test->assertTrue($fs->isPrivate($null));
    }
}
