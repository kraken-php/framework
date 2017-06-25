<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\WriteException;

trait FsApiCopyDirPartial
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
    public function testApiCopyDir_ThrowsException_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('FILE_A');
        $pnull = $this->getPrefixed('NULL');

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($pa));
        $test->assertFalse($fs->exists($pnull));

        $fs->copyDir($pa, $pnull);
    }

    /**
     *
     */
    public function testApiCopyDir_CopiesNode_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('DIR_A');
        $pnull = $this->getPrefixed('NULL');

        $test->assertTrue($fs->exists($pa));
        $test->assertFalse($fs->exists($pnull));

        $fs->copyDir($pa, $pnull);

        $test->assertTrue($fs->exists($pnull));
        $test->assertEquals($fs->read($pa), $fs->read($pnull));
    }

    /**
     *
     */
    public function testApiCopyDir_ThrowsException_WhenNodeIsDirectoryAndDestinationIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $da = $this->getPrefixed('DIR_A');
        $fa = $this->getPrefixed('FILE_A');

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($da));
        $test->assertTrue($fs->exists($fa));

        $fs->copyDir($da, $fa);
    }


    /**
     *
     */
    public function testApiCopyDir_ThrowsException_WhenNodeIsDirectoryAndDestinationIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $da = $this->getPrefixed('DIR_A');
        $db = $this->getPrefixed('DIR_B');

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($da));
        $test->assertTrue($fs->exists($db));

        $fs->copyDir($da, $db);
    }

    /**
     *
     */
    public function testApiCopyDir_ThrowsException_WhenSourceDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $dnull = $this->getPrefixed('DIR_NULL');
        $null = $this->getPrefixed('NULL');

        $test->setExpectedException(WriteException::class);
        $test->assertFalse($fs->exists($dnull));
        $test->assertFalse($fs->exists($null));

        $fs->copyDir($dnull, $null);
    }
}
