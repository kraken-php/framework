<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

trait FsApiMovePartial
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
    public function testApiMove_MovesFile_WhenDestinationDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('FILE_A');
        $to   = $this->getPrefixed('FILE_NULL');

        $test->assertFalse($fs->exists($to));
        $fs->move($from, $to);

        $test->assertTrue($fs->exists($to));
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenSourceIsFileAndDestinationIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('FILE_A');
        $to   = $this->getPrefixed('FILE_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($to));
        $fs->move($from, $to);
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenSourceIsFileAndDestinationIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('FILE_A');
        $to   = $this->getPrefixed('DIR_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($to));
        $fs->move($from, $to);
    }

    /**
     *
     */
    public function testApiMove_MovesDirectory_WhenDestinationDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('DIR_A');
        $to   = $this->getPrefixed('DIR_NULL');
        $ch   = $this->getPrefixed('FILE_C', 'DIR_A', 'DIR_NULL');

        $test->assertFalse($fs->exists($to));
        $fs->move($from, $to);

        $test->assertTrue($fs->exists($to));
        $test->assertTrue($fs->exists($ch));
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenSourceIsDirectoryAndDestinationIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('DIR_A');
        $to   = $this->getPrefixed('FILE_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($to));
        $fs->move($from, $to);
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenSourceIsDirectoryAndDestinationIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('DIR_A');
        $to   = $this->getPrefixed('DIR_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($to));
        $fs->move($from, $to);
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenSourceDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $from = $this->getPrefixed('NULL');
        $to   = $this->getPrefixed('NULL');

        $test->setExpectedException(IoWriteException::class);
        $fs->move($from, $to);
    }
}
