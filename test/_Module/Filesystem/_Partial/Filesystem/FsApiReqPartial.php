<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\ReadException;

trait FsApiReqPartial
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
    public function testApiReq_RequiresContents_WhenPathDoesExistAndIsValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $contents = 'FILE_E_TEXT';

        $test->assertEquals($contents, $fs->req($this->getPrefixed('FILE_E')));
    }

    /**
     *
     */
    public function testApiReq_ReturnsEmptyString_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $contents = '';

        $test->assertEquals($contents, $fs->req($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiReq_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);

        $fs->req($this->getPrefixed('FILE_NULL'));
    }
}
