<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\ReadException;

trait FsApiGetVisibilityPartial
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
    public function testApiGetVisibility_ReturnsPublic_WhenFileDoesExistAndIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals('public', $fs->getVisibility($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiGetVisibility_ReturnsPrivate_WhenFileDoesExistAndIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals('private', $fs->getVisibility($this->getPrefixed('FILE_D')));
    }

    /**
     *
     */
    public function testApiGetVisibility_ReturnsPublic_WhenDirectoryDoesExistAndIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals('public', $fs->getVisibility($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiGetVisibility_ReturnsPrivate_WhenDirectoryDoesExistAndIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals('private', $fs->getVisibility($this->getPrefixed('DIR_D')));
    }

    /**
     *
     */
    public function testApiGetVisibility_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);

        $fs->getVisibility($this->getPrefixed('DIR_NULL'));
    }
}
