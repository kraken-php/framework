<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;

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
        $contents = $this->encodeReq('FILE_A_TEXT');

        $test->assertEquals($contents, $fs->req($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiReq_ReturnsEmptyString_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $contents = $this->encodeReq('');

        $test->assertEquals($contents, $fs->req($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiReq_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(IoReadException::class);

        $fs->req($this->getPrefixed('FILE_NULL'));
    }

    /**
     * @param string $str
     * @return string
     */
    private function encodeReq($str)
    {
        return "data://text/plain;base64," . base64_encode($str);
    }
}
