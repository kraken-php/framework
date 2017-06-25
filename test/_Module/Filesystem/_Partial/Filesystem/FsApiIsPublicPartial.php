<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\ReadException;

trait FsApiIsPublicPartial
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
    public function testApiIsPublic_ReturnsTrue_WhenFileDoesExistAndIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertTrue($fs->isPublic($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiIsPublic_ReturnsFalse_WhenFileDoesExistAndIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertFalse($fs->isPublic($this->getPrefixed('FILE_D')));
    }

    /**
     *
     */
    public function testApiIsPublic_ReturnsTrue_WhenDirectoryDoesExistAndIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertTrue($fs->isPublic($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiIsPublic_ReturnsFalse_WhenDirectoryDoesExistAndIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertFalse($fs->isPublic($this->getPrefixed('DIR_D')));
    }

    /**
     *
     */
    public function testApiIsPublic_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);

        $fs->isPublic($this->getPrefixed('DIR_NULL'));
    }
}
