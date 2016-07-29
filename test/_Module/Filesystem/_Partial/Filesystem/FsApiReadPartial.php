<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;

trait FsApiReadPartial
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
    public function testApiRead_ReadsContents_WhenPathDoesExistAndIsValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $contents = 'FILE_A_TEXT';

        $test->assertEquals($contents, $fs->read($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiRead_ReturnsEmptyString_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $contents = '';

        $test->assertEquals($contents, $fs->read($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiRead_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(IoReadException::class);

        $fs->read($this->getPrefixed('FILE_NULL'));
    }
}
