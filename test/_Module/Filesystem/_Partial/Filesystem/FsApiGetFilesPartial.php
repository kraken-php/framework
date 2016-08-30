<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\ReadException;

trait FsApiGetFilesPartial
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
    public function testApiGetFiles_ReturnsContentsOnRoot_WhenNoDirectorySet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        if ($this->getPrefix() !== '')
        {
            return; // Fs behaviour only!
        }

        $test->assertSame($this->getPathData('', 'file'), $fs->getFiles());
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContentsOnRoot_WhenEmptyStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'file'), $fs->getFiles($this->getPrefixed('')));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContentsOnRoot_WhenRootStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'file'), $fs->getFiles($this->getPrefixed('/')));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContents_WhenPathDoesExistAndIsValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('DIR_A', 'file'), $fs->getFiles($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsEmptyArray_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame([], $fs->getFiles($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsEmptyArray_WhenDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);
        $fs->getFiles($this->getPrefixed('DIR_NULL'));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContents_WhenFlagIsRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'file', true), $fs->getFiles($this->getPrefixed(''), true));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContents_WhenFlagIsNotRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'file', false), $fs->getFiles($this->getPrefixed(''), false));
    }

    /**
     *
     */
    public function testApiGetFiles_ReturnsContents_WhenFilepatternIsSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $filePattern = '#^DIR_#si';

        $test->assertSame($this->getPathData('', 'file', true, $filePattern), $fs->getFiles($this->getPrefixed(''), true, $filePattern));
    }
}
