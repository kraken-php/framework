<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\ReadException;

trait FsApiGetContentsPartial
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
    public function testApiGetContents_ReturnsContentsOnRoot_WhenNoDirectorySet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        if ($this->getPrefix() !== '')
        {
            return; // Fs behaviour only!
        }

        $test->assertSame($this->getPathData(), $fs->getContents());
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContentsOnRoot_WhenEmptyStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData(), $fs->getContents($this->getPrefixed('')));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContentsOnRoot_WhenRootStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData(), $fs->getContents($this->getPrefixed('/')));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContents_WhenPathDoesExistAndIsValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('DIR_A'), $fs->getContents($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsEmptyArray_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame([], $fs->getContents($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsEmptyArray_WhenDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);
        $fs->getContents($this->getPrefixed('DIR_NULL'));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContents_WhenFlagIsRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', '', true), $fs->getContents($this->getPrefixed(''), true));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContents_WhenFlagIsNotRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', '', false), $fs->getContents($this->getPrefixed(''), false));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContents_WhenFilepatternIsSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $filePattern = '#^DIR_#si';

        $test->assertSame($this->getPathData('', '', true, $filePattern), $fs->getContents($this->getPrefixed(''), true, $filePattern));
    }

    /**
     *
     */
    public function testApiGetContents_ReturnsContents_WhenMultipleFilepatternsAreSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $filePatterns = [ '#^DIR_#si', '#_A$#si' ];

        $test->assertSame($this->getPathData('', '', true, $filePatterns), $fs->getContents($this->getPrefixed(''), true, $filePatterns));
    }
}
